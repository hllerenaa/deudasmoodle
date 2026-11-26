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
 * Ejecuta las acciones de las reglas en cada require_login: bloquear plataforma,
 * bloqueo global de actividades y avisos en Area personal / cursos.
 *
 * Las reglas "bloquear recursos" cubren actividades concretas (por cmid) y/o todas las que
 * cumplan los filtros de tipo, categoria y curso.
 *
 * @package    local_reglasdeuda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class aplicador {

    /**
     * Paginas que nunca se bloquean: las del plugin y los flujos forzados de Moodle (politicas, MFA,
     * cambio de contrasena, perfil obligatorio). Bloquearlas provoca bucles de redireccion.
     * Las que terminan en '/' son prefijos.
     */
    const SCRIPTS_LIBRES = [
        '/local/reglasdeuda/',
        '/admin/tool/policy/',
        '/admin/tool/mfa/',
        '/login/',
        '/user/policy.php',
        '/user/edit.php',
        '/user/editadvanced.php',
        '/course/switchrole.php', // "Volver a mi rol normal".
    ];

    /** Paginas donde se muestra el aviso de las reglas "solo mensaje". */
    const SCRIPTS_AVISO = ['/index.php', '/my/index.php', '/my/courses.php', '/course/view.php'];

    /** @var array claves ya procesadas en esta peticion (require_login puede llamarse varias veces). */
    private static $hecho = [];

    /**
     * Punto de entrada desde local_reglasdeuda_after_require_login().
     *
     * @param mixed $courseorid curso, id o null
     * @param \cm_info|\stdClass|null $cm
     * @param bool $preventredirect true en AJAX/WS/app: no se puede redirigir, se lanza excepcion
     * @throws \moodle_exception componente local_reglasdeuda cuando no se puede redirigir
     */
    public static function despues_de_login($courseorid, $cm, bool $preventredirect): void {
        global $USER, $SCRIPT, $CFG;

        if (CLI_SCRIPT || during_initial_install() || !empty($CFG->upgraderunning)) {
            return;
        }
        if (!isloggedin() || isguestuser()) {
            return;
        }
        if ((defined('NO_SITEPOLICY_CHECK') && NO_SITEPOLICY_CHECK) || self::script_libre((string)$SCRIPT)) {
            return;
        }
        self::ir_a_configuracion_tras_instalar($preventredirect);
        if (!get_config('local_reglasdeuda', 'habilitado')) {
            return;
        }
        $reglas = reglas::activas();
        if (!$reglas) {
            return;
        }

        // App movil, servicios web y AJAX: no hay redireccion posible; se corta con una excepcion.
        $sinredireccion = $preventredirect || AJAX_SCRIPT || (defined('WS_SERVER') && WS_SERVER);
        $curso = self::curso($courseorid);
        $contexto = ['curso' => $curso, 'cmid' => $cm ? (int)$cm->id : null];

        if (self::primera_vez('bloquear')) {
            foreach (reglas::activas_por_accion(reglas::ACCION_BLOQUEAR) as $regla) {
                if (self::aplica($regla, $contexto)) {
                    self::cortar($sinredireccion, new \moodle_url('/local/reglasdeuda/bloqueado.php', ['regla' => $regla->id]));
                }
            }
        }

        if ($cm && $curso && self::primera_vez('cm' . $cm->id)) {
            self::bloquear_actividad($cm, $curso, $contexto, $sinredireccion);
        }

    }

    /**
     * Bloqueo de recursos: la actividad la eligio el docente (en su configuracion) o el admin (IDs o filtros).
     * En modo "entregar" (tarea/cuestionario) se puede ver la actividad, con el aviso; se bloquea solo
     * iniciar el intento o agregar/enviar la entrega.
     *
     * @param \cm_info|\stdClass $cm
     * @param \stdClass $curso
     * @param array $contexto
     * @param bool $sinredireccion
     */
    private static function bloquear_actividad($cm, \stdClass $curso, array $contexto, bool $sinredireccion): void {
        global $SCRIPT, $USER;
        $config = recursos::de_actividad((int)$cm->id);
        foreach (recursos::reglas() as $regla) {
            $modo = null;
            if ($config && (int)$config->reglaid === (int)$regla->id) {
                $modo = $config->modo;
            } else if (self::cubre_actividad($regla, $cm, $curso)) {
                $modo = recursos::MODO_ENTRAR;
            }
            if ($modo === null || !self::aplica($regla, $contexto, $evaluacion)) {
                continue;
            }
            $modname = $cm->modname ?? '';
            $bloquear = $modo === recursos::MODO_ENTRAR
                || !in_array($modname, recursos::MODULOS_ENTREGA, true)
                || recursos::es_accion_de_entrega($modname, (string)$SCRIPT);
            if ($bloquear) {
                self::cortar($sinredireccion, new \moodle_url('/local/reglasdeuda/bloqueado.php',
                    ['regla' => $regla->id, 'cmid' => $cm->id]));
            }
            if (!$sinredireccion && self::primera_vez('avisocm' . $cm->id)) {
                // Puede ver la actividad, pero se le avisa que no podra entregar/iniciar.
                $c = mensaje::contexto($regla, $evaluacion, $USER, $contexto);
                \core\notification::add(mensaje::html($c, false), mensaje::NOTIFICACION[$c['estilo']]);
            }
        }
    }

    /**
     * Avisos de las reglas "solo mensaje" en Pagina principal, Area personal, Mis cursos y cursos.
     *
     * Se llama antes de enviar la cabecera de la pagina (before_http_headers): asi tambien funciona
     * en la portada, que no pasa por require_login, y las notificaciones aun no se han pintado.
     */
    public static function avisos_pagina(): void {
        global $USER, $SCRIPT, $CFG, $PAGE;
        if (CLI_SCRIPT || AJAX_SCRIPT || during_initial_install() || !empty($CFG->upgraderunning)) {
            return;
        }
        if (!isloggedin() || isguestuser() || !get_config('local_reglasdeuda', 'habilitado')) {
            return;
        }
        if (!in_array($SCRIPT, self::SCRIPTS_AVISO, true) || !self::primera_vez('avisar')) {
            return;
        }
        $curso = ($PAGE->course && $PAGE->course->id != SITEID) ? $PAGE->course : null;
        $contexto = ['curso' => $curso, 'cmid' => null];
        foreach (reglas::activas_por_accion(reglas::ACCION_AVISAR) as $regla) {
            if (self::aplica($regla, $contexto, $evaluacion)) {
                $c = mensaje::contexto($regla, $evaluacion, $USER, $contexto);
                \core\notification::add(mensaje::html($c, false), mensaje::NOTIFICACION[$c['estilo']]);
            }
        }
    }

    /**
     * Tras instalar o actualizar, lleva al administrador a la pantalla del plugin una sola vez,
     * cuando Moodle ya no tiene actualizaciones pendientes.
     *
     * @param bool $preventredirect
     */
    private static function ir_a_configuracion_tras_instalar(bool $preventredirect): void {
        if ($preventredirect || AJAX_SCRIPT || (defined('WS_SERVER') && WS_SERVER) || !is_siteadmin()) {
            return;
        }
        if (!get_config('local_reglasdeuda', 'irconfiguracion') || (function_exists('moodle_needs_upgrading') && moodle_needs_upgrading())) {
            return;
        }
        unset_config('irconfiguracion', 'local_reglasdeuda');
        redirect(new \moodle_url('/local/reglasdeuda/index.php'));
    }

    /**
     * La regla de restriccion cubre esta actividad: esta en la lista concreta o cumple los filtros.
     *
     * @param \stdClass $regla
     * @param \cm_info|\stdClass $cm
     * @param \stdClass $curso
     * @return bool
     */
    public static function cubre_actividad(\stdClass $regla, $cm, \stdClass $curso): bool {
        if (in_array((int)$cm->id, array_map('intval', reglas::csv($regla->actividades)), true)) {
            return true;
        }
        if (empty($regla->globalactivo)) {
            return false;
        }
        $cursos = array_map('intval', reglas::csv($regla->globalcursos));
        if ($cursos && !in_array((int)$curso->id, $cursos, true)) {
            return false;
        }
        $modulos = reglas::csv($regla->globalmodulos);
        $modname = $cm->modname ?? '';
        if ($modulos && !in_array($modname, $modulos, true)) {
            return false;
        }
        $categorias = array_map('intval', reglas::csv($regla->globalcategorias));
        if ($categorias && !self::en_categorias((int)$curso->category, $categorias)) {
            return false;
        }
        return true;
    }

    /**
     * @param string $script
     * @return bool
     */
    private static function script_libre(string $script): bool {
        foreach (self::SCRIPTS_LIBRES as $libre) {
            if (substr($libre, -1) === '/' ? strpos($script, $libre) === 0 : $script === $libre) {
                return true;
            }
        }
        return false;
    }

    /**
     * Redirige a la pagina de bloqueo o, si no se puede (AJAX/WS/app), lanza la excepcion.
     *
     * @param bool $sinredireccion
     * @param \moodle_url $url
     */
    private static function cortar(bool $sinredireccion, \moodle_url $url): void {
        if ($sinredireccion) {
            throw new \moodle_exception('accesorestringido', 'local_reglasdeuda');
        }
        redirect($url);
    }

    /**
     * La categoria o alguna de sus categorias padre esta en la lista.
     *
     * @param int $categoriaid
     * @param int[] $categorias
     * @return bool
     */
    private static function en_categorias(int $categoriaid, array $categorias): bool {
        $categoria = \core_course_category::get($categoriaid, IGNORE_MISSING, true);
        if (!$categoria) {
            return false;
        }
        $ruta = array_map('intval', array_filter(explode('/', $categoria->path)));
        return (bool)array_intersect($ruta, $categorias);
    }

    /**
     * @param \stdClass $regla
     * @param array $contexto
     * @param array|null $evaluacion salida: resultado de la evaluacion
     * @return bool
     */
    private static function aplica(\stdClass $regla, array $contexto, ?array &$evaluacion = null): bool {
        global $USER;
        if (evaluador::esta_exento($regla, (int)$USER->id)) {
            return false;
        }
        $evaluacion = evaluador::evaluar($regla, $USER, $contexto);
        return $evaluacion['aplica'];
    }

    /**
     * @param string $clave
     * @return bool
     */
    private static function primera_vez(string $clave): bool {
        if (isset(self::$hecho[$clave])) {
            return false;
        }
        self::$hecho[$clave] = true;
        return true;
    }

    /**
     * @param mixed $courseorid
     * @return \stdClass|null curso (null para la portada o sin curso)
     */
    private static function curso($courseorid): ?\stdClass {
        if (is_object($courseorid)) {
            return $courseorid->id == SITEID ? null : $courseorid;
        }
        if ($courseorid && $courseorid != SITEID) {
            return get_course((int)$courseorid);
        }
        return null;
    }
}
