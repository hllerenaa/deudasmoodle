<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace local_reglasdeuda\local;

/**
 * Arma el mensaje (titulo, cuerpo, boton) de una regla para un usuario.
 *
 * @package    local_reglasdeuda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mensaje {

    /** Estilo de la regla => tipo de \core\notification. */
    const NOTIFICACION = [
        'info' => \core\output\notification::NOTIFY_INFO,
        'warning' => \core\output\notification::NOTIFY_WARNING,
        'danger' => \core\output\notification::NOTIFY_ERROR,
    ];

    /**
     * Contexto para la plantilla local_reglasdeuda/mensaje.
     *
     * @param \stdClass $regla
     * @param array $evaluacion resultado de evaluador::evaluar()
     * @param \stdClass $usuario
     * @param array $contexto ['curso' => ..., 'cmid' => ...]
     * @param bool $converificar mostrar el boton "Ya pague, volver a verificar"
     * @return array
     */
    public static function contexto(\stdClass $regla, array $evaluacion, \stdClass $usuario, array $contexto = [],
            bool $converificar = true): array {
        $variables = plantilla::variables_usuario($regla, $usuario, $contexto) + $evaluacion['datos'];
        $sistema = \context_system::instance();

        $titulo = plantilla::reemplazar((string)$regla->titulo, $variables);
        if ($evaluacion['mensajeapi'] !== '') {
            // El mensaje de la API es texto plano: se escapa completo.
            $cuerpo = nl2br(s($evaluacion['mensajeapi']));
        } else {
            // Los valores se escapan antes de insertarse en el HTML del admin; luego se limpia el conjunto.
            $html = plantilla::reemplazar((string)$regla->mensaje, $variables, 's');
            $cuerpo = format_text($html, $regla->mensajeformat, ['context' => $sistema]);
        }

        $estilo = array_key_exists($regla->estilo, self::NOTIFICACION) ? $regla->estilo : 'warning';
        $verificar = new \moodle_url('/local/reglasdeuda/verificar.php', ['sesskey' => sesskey()]);
        return [
            'titulo' => format_string($titulo, true, ['context' => $sistema]),
            'cuerpo' => $cuerpo,
            'botontexto' => format_string(plantilla::reemplazar((string)$regla->botontexto, $variables), true,
                ['context' => $sistema]),
            'botonurl' => self::url_boton($regla, $variables),
            'nuevapestana' => !empty($regla->nuevapestana),
            'estilo' => $estilo,
            'alerta' => $estilo === 'danger' ? 'alert-danger' : ($estilo === 'info' ? 'alert-info' : 'alert-warning'),
            'verificarurl' => $converificar ? $verificar->out(false) : '',
        ];
    }

    /**
     * HTML del mensaje. Usa html_writer (no plantillas) porque se genera dentro de require_login,
     * antes de que la pagina inicialice el tema y el renderer.
     *
     * @param array $c de contexto()
     * @param bool $conalerta envolver en caja de alerta (no en notificaciones, que ya la traen)
     * @return string
     */
    public static function html(array $c, bool $conalerta = true): string {
        $partes = '';
        if ($c['titulo'] !== '') {
            $partes .= \html_writer::tag('h4', $c['titulo'], ['class' => 'local-reglasdeuda-titulo']);
        }
        $partes .= \html_writer::div($c['cuerpo'], 'local-reglasdeuda-cuerpo');
        $botones = '';
        if ($c['botonurl'] !== '' && $c['botontexto'] !== '') {
            $atributos = ['class' => 'btn btn-primary mt-2 me-2 mr-2'];
            if ($c['nuevapestana']) {
                $atributos += ['target' => '_blank', 'rel' => 'noopener noreferrer'];
            }
            $botones .= \html_writer::link($c['botonurl'], $c['botontexto'], $atributos);
        }
        if ($c['verificarurl'] !== '') {
            $botones .= \html_writer::link($c['verificarurl'], get_string('verificar', 'local_reglasdeuda'),
                ['class' => 'btn btn-secondary mt-2']);
        }
        if ($botones !== '') {
            $partes .= \html_writer::div($botones, 'local-reglasdeuda-botones');
        }
        $clase = 'local-reglasdeuda-mensaje' . ($conalerta ? ' alert ' . $c['alerta'] : '');
        return \html_writer::div($partes, $clase, ['role' => 'alert']);
    }

    /**
     * URL del boton: la configurada (con variables) o la de respaldo; '' si ninguna es valida.
     *
     * @param \stdClass $regla
     * @param array $variables
     * @return string
     */
    public static function url_boton(\stdClass $regla, array $variables): string {
        foreach ([(string)$regla->botonurl, (string)$regla->botonurlrespaldo] as $candidata) {
            $url = trim(plantilla::reemplazar($candidata, $variables));
            if (self::es_url_web($url)) {
                return $url;
            }
        }
        return '';
    }

    /**
     * Solo http(s): evita enlaces javascript: o data: que vengan de la API.
     *
     * @param string $url
     * @return bool
     */
    public static function es_url_web(string $url): bool {
        return $url !== '' && preg_match('~^https?://~i', $url) && clean_param($url, PARAM_URL) === $url;
    }
}
