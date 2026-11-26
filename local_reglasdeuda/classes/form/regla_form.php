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

namespace local_reglasdeuda\form;

use local_reglasdeuda\local\cliente_api;
use local_reglasdeuda\local\mensaje;
use local_reglasdeuda\local\parametros;
use local_reglasdeuda\local\plantilla;
use local_reglasdeuda\local\reglas;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Formulario de una regla.
 *
 * @package    local_reglasdeuda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class regla_form extends \moodleform {

    /**
     * Opciones del editor del mensaje (sin archivos: el mensaje se muestra fuera de cualquier area de archivos).
     *
     * @return array
     */
    public static function opciones_editor(): array {
        return ['maxfiles' => 0, 'noclean' => false, 'context' => \context_system::instance()];
    }

    protected function definition() {
        $f = $this->_form;
        $c = 'local_reglasdeuda';

        $f->addElement('hidden', 'id');
        $f->setType('id', PARAM_INT);

        // General.
        $f->addElement('header', 'h_general', get_string('general'));
        $f->addElement('text', 'nombre', get_string('nombre', $c), ['size' => 50]);
        $f->setType('nombre', PARAM_TEXT);
        $f->addRule('nombre', null, 'required', null, 'client');
        $f->addRule('nombre', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $f->addElement('advcheckbox', 'activa', get_string('activa', $c));
        $f->addElement('text', 'orden', get_string('orden', $c), ['size' => 5]);
        $f->setType('orden', PARAM_INT);
        $f->addHelpButton('orden', 'orden', $c);

        // API.
        $f->addElement('header', 'h_api', get_string('paso_api', $c));
        $f->setExpanded('h_api');
        $f->addElement('text', 'url', get_string('url', $c), ['size' => 70]);
        $f->setType('url', PARAM_RAW_TRIMMED);
        $f->addRule('url', null, 'required', null, 'client');
        $f->addElement('select', 'metodo', get_string('metodo', $c), cliente_api::metodos());

        // Parametros enviados: nombre <- campo de Moodle (o valor fijo), una fila por parametro.
        $f->addElement('static', 'parametros_titulo', \html_writer::tag('strong', get_string('parametros', $c)));
        $fila = [
            $f->createElement('text', 'param_nombre', get_string('param_nombre', $c),
                ['size' => 18, 'placeholder' => get_string('param_nombre', $c)]),
            $f->createElement('select', 'param_campo', get_string('param_campo', $c),
                ['' => get_string('choosedots')] + parametros::campos_disponibles()),
            $f->createElement('text', 'param_valor', get_string('param_valor', $c),
                ['size' => 16, 'placeholder' => get_string('param_valor_ph', $c)]),
        ];
        $grupo = $f->createElement('group', 'param_grupo', get_string('parametro', $c), $fila, ' ← ', false);
        $this->repeat_elements([$grupo], max(1, (int)($this->_customdata['numparametros'] ?? 1)), [
            'param_nombre' => ['type' => PARAM_RAW_TRIMMED],
            'param_valor' => ['type' => PARAM_RAW_TRIMMED],
            'param_grupo' => ['helpbutton' => ['parametro', $c]],
        ], 'param_repeticiones', 'param_agregar', 1, get_string('agregarparametro', $c), true);

        $f->addElement('select', 'autenticacion', get_string('autenticacion', $c), [
            'ninguna' => get_string('autenticacion_ninguna', $c),
            'bearer' => get_string('autenticacion_bearer', $c),
            'cabecera' => get_string('autenticacion_cabecera', $c),
        ]);
        $f->addElement('text', 'cabeceranombre', get_string('cabeceranombre', $c), ['size' => 30]);
        $f->setType('cabeceranombre', PARAM_RAW_TRIMMED);
        $f->hideIf('cabeceranombre', 'autenticacion', 'neq', 'cabecera');
        $f->addElement('passwordunmask', 'token', get_string('token', $c), ['size' => 50]);
        $f->setType('token', PARAM_RAW_TRIMMED);
        $f->hideIf('token', 'autenticacion', 'eq', 'ninguna');
        $f->addElement('text', 'timeout', get_string('timeout', $c), ['size' => 4]);
        $f->setType('timeout', PARAM_INT);
        $f->setAdvanced('timeout');

        // Respuesta: a la vista solo lo esencial; el resto en "Mostrar mas..." con valores por defecto.
        $f->addElement('header', 'h_respuesta', get_string('paso_respuesta', $c));
        $f->setExpanded('h_respuesta');
        $f->addElement('static', 'respuesta_ejemplo', get_string('respuesta_esperada', $c),
            \html_writer::tag('code', s('{"resultado": true, "datos": {"total": "235.50", "url_pago": "https://..."}}')));
        $f->addElement('text', 'rutaresultado', get_string('rutaresultado', $c), ['size' => 20]);
        $f->setType('rutaresultado', PARAM_RAW_TRIMMED);
        $f->addHelpButton('rutaresultado', 'rutaresultado', $c);
        $f->addGroup([
            $f->createElement('radio', 'valoractiva', '', get_string('valoractiva_true', $c), 1),
            $f->createElement('radio', 'valoractiva', '', get_string('valoractiva_false', $c), 0),
        ], 'g_valoractiva', get_string('valoractiva', $c), ' ', false);
        $f->addHelpButton('g_valoractiva', 'valoractiva', $c);
        $f->addElement('select', 'errorpolitica', get_string('errorpolitica', $c), [
            'permitir' => get_string('errorpolitica_permitir', $c),
            'aplicar' => get_string('errorpolitica_aplicar', $c),
        ]);
        $f->addHelpButton('errorpolitica', 'errorpolitica', $c);
        $f->addElement('duration', 'cachettl', get_string('cachettl', $c), ['optional' => false, 'defaultunit' => MINSECS]);
        $f->addHelpButton('cachettl', 'cachettl', $c);
        $f->addElement('text', 'rutadatos', get_string('rutadatos', $c), ['size' => 20]);
        $f->setType('rutadatos', PARAM_RAW_TRIMMED);
        $f->addHelpButton('rutadatos', 'rutadatos', $c);
        $f->setAdvanced('rutadatos');
        $f->addElement('text', 'rutamensaje', get_string('rutamensaje', $c), ['size' => 20]);
        $f->setType('rutamensaje', PARAM_RAW_TRIMMED);
        $f->addHelpButton('rutamensaje', 'rutamensaje', $c);
        $f->setAdvanced('rutamensaje');
        $f->addElement('textarea', 'respuestaejemplo', get_string('respuestaejemplo', $c),
            ['rows' => 8, 'cols' => 70, 'class' => 'text-monospace font-monospace']);
        $f->setType('respuestaejemplo', PARAM_RAW);
        $f->addHelpButton('respuestaejemplo', 'respuestaejemplo', $c);
        $f->setAdvanced('respuestaejemplo');

        // Accion.
        $f->addElement('header', 'h_accion', get_string('paso_accion', $c));
        $f->setExpanded('h_accion');
        $f->addElement('select', 'accion', get_string('accion', $c), reglas::opciones_accion());
        $f->addHelpButton('accion', 'accion', $c);
        $f->addElement('text', 'actividades', get_string('actividades', $c), ['size' => 40]);
        $f->setType('actividades', PARAM_RAW_TRIMMED);
        $f->addHelpButton('actividades', 'actividades', $c);
        $f->hideIf('actividades', 'accion', 'neq', reglas::ACCION_RESTRINGIR);
        $f->addElement('advcheckbox', 'globalactivo', get_string('globalactivo', $c));
        $f->addHelpButton('globalactivo', 'globalactivo', $c);
        $f->hideIf('globalactivo', 'accion', 'neq', reglas::ACCION_RESTRINGIR);
        $f->addElement('autocomplete', 'globalmodulos', get_string('globalmodulos', $c), self::modulos(),
            ['multiple' => true, 'noselectionstring' => get_string('todos', $c)]);
        $f->hideIf('globalmodulos', 'accion', 'neq', reglas::ACCION_RESTRINGIR);
        $f->hideIf('globalmodulos', 'globalactivo', 'notchecked');
        $f->addElement('autocomplete', 'globalcategorias', get_string('globalcategorias', $c),
            \core_course_category::make_categories_list(), ['multiple' => true, 'noselectionstring' => get_string('todas', $c)]);
        $f->addHelpButton('globalcategorias', 'globalcategorias', $c);
        $f->hideIf('globalcategorias', 'accion', 'neq', reglas::ACCION_RESTRINGIR);
        $f->hideIf('globalcategorias', 'globalactivo', 'notchecked');
        $f->addElement('course', 'globalcursos', get_string('globalcursos', $c),
            ['multiple' => true, 'includefrontpage' => false, 'noselectionstring' => get_string('todos', $c)]);
        $f->addHelpButton('globalcursos', 'globalcursos', $c);
        $f->hideIf('globalcursos', 'accion', 'neq', reglas::ACCION_RESTRINGIR);
        $f->hideIf('globalcursos', 'globalactivo', 'notchecked');
        $f->addElement('autocomplete', 'rolesaplica', get_string('rolesaplica', $c), self::roles(),
            ['multiple' => true, 'noselectionstring' => get_string('todos', $c)]);
        $f->addHelpButton('rolesaplica', 'rolesaplica', $c);

        // Mensaje.
        $f->addElement('header', 'h_mensaje', get_string('paso_mensaje', $c));
        $f->setExpanded('h_mensaje');
        $f->addElement('static', 'variables', get_string('variables', $c),
            \html_writer::div('', 'local-reglasdeuda-variables',
                ['id' => 'local-reglasdeuda-variables', 'data-config' => json_encode(self::config_variables())]) .
            \html_writer::div(get_string('variables_ayuda', $c), 'form-text text-muted small'));
        $f->addElement('text', 'titulo', get_string('titulo', $c), ['size' => 60]);
        $f->setType('titulo', PARAM_TEXT);
        $f->addElement('editor', 'mensaje_editor', get_string('mensaje', $c), ['rows' => 8], self::opciones_editor());
        $f->setType('mensaje_editor', PARAM_RAW);
        $f->addElement('text', 'botontexto', get_string('botontexto', $c), ['size' => 30]);
        $f->setType('botontexto', PARAM_TEXT);
        $f->addElement('text', 'botonurl', get_string('botonurl', $c), ['size' => 60]);
        $f->setType('botonurl', PARAM_RAW_TRIMMED);
        $f->addHelpButton('botonurl', 'botonurl', $c);
        $f->addElement('text', 'botonurlrespaldo', get_string('botonurlrespaldo', $c), ['size' => 60]);
        $f->setType('botonurlrespaldo', PARAM_RAW_TRIMMED);
        $f->addElement('advcheckbox', 'nuevapestana', get_string('nuevapestana', $c));
        $f->addElement('select', 'estilo', get_string('estilo', $c), [
            'info' => get_string('estilo_info', $c),
            'warning' => get_string('estilo_warning', $c),
            'danger' => get_string('estilo_danger', $c),
        ]);

        $this->add_action_buttons();
    }

    public function validation($data, $files) {
        global $DB;
        $errores = parent::validation($data, $files);
        $c = 'local_reglasdeuda';

        if (!preg_match('~^https?://~i', $data['url']) || clean_param($data['url'], PARAM_URL) !== $data['url']) {
            $errores['url'] = get_string('error_url', $c);
        }
        if ($data['autenticacion'] === 'cabecera' && !cliente_api::nombre_cabecera_valido($data['cabeceranombre'])) {
            $errores['cabeceranombre'] = get_string('error_cabecera', $c);
        }
        if ($data['timeout'] < 1 || $data['timeout'] > 30) {
            $errores['timeout'] = get_string('error_timeout', $c);
        }
        $vistos = [];
        foreach (self::parametros_enviados($data) as $i => $p) {
            $clave = "param_grupo[$i]";
            if (!parametros::nombre_valido($p['nombre'])) {
                $errores[$clave] = get_string('error_param_nombre', $c);
            } else if (isset($vistos[$p['nombre']])) {
                $errores[$clave] = get_string('error_param_repetido', $c);
            } else if ($p['campo'] === '') {
                $errores[$clave] = get_string('error_param_sinvalor', $c);
            }
            $vistos[$p['nombre']] = true;
        }
        if (!self::parametros_enviados($data)) {
            $errores['param_grupo[0]'] = get_string('error_sinparametros', $c);
        }
        if (trim($data['respuestaejemplo']) !== '' && !is_array(json_decode($data['respuestaejemplo'], true))) {
            $errores['respuestaejemplo'] = get_string('error_json', $c);
        }
        foreach (['rutaresultado', 'rutadatos', 'rutamensaje'] as $campo) {
            // Datos y mensaje pueden quedar vacios (no se usan); el resultado es obligatorio.
            if ($campo !== 'rutaresultado' && $data[$campo] === '') {
                continue;
            }
            if (!preg_match('/^[a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*$/', $data[$campo])) {
                $errores[$campo] = get_string('error_ruta', $c);
            }
        }
        if (trim($data['botonurlrespaldo']) !== '' && !mensaje::es_url_web(trim($data['botonurlrespaldo']))) {
            $errores['botonurlrespaldo'] = get_string('error_url', $c);
        }
        if ($data['accion'] === reglas::ACCION_RESTRINGIR) {
            $cmids = reglas::csv($data['actividades']);
            $novalidos = array_filter($cmids, function($v) {
                return !ctype_digit($v);
            });
            if ($novalidos) {
                $errores['actividades'] = get_string('error_actividades', $c);
            } else if ($cmids && count(array_unique($cmids)) !== $DB->count_records_list('course_modules', 'id', $cmids)) {
                $errores['actividades'] = get_string('error_actividadesnoexisten', $c);
            } else if (!$cmids && empty($data['globalactivo'])) {
                $errores['actividades'] = get_string('error_sinalcance', $c);
            }
        }
        if ($data['cachettl'] < reglas::TTL_MINIMO || $data['cachettl'] > reglas::TTL_MAXIMO) {
            $errores['cachettl'] = get_string('error_cachettl', $c);
        }
        if (preg_match('/[\r\n]/', $data['token'] ?? '')) {
            $errores['token'] = get_string('error_token', $c);
        }
        return $errores;
    }

    /**
     * Filas de parametros con nombre (las filas vacias se ignoran), conservando el indice de la fila.
     *
     * @param array|\stdClass $data datos del formulario
     * @return array indice => ['nombre', 'campo', 'valor']
     */
    public static function parametros_enviados($data): array {
        $data = (array)$data;
        $filas = [];
        foreach ((array)($data['param_nombre'] ?? []) as $i => $nombre) {
            $nombre = trim((string)$nombre);
            if ($nombre === '') {
                continue;
            }
            $campo = (string)($data['param_campo'][$i] ?? '');
            $valor = trim((string)($data['param_valor'][$i] ?? ''));
            if ($campo === '' && $valor !== '') {
                $campo = parametros::FIJO; // Solo se escribio un valor: se envia tal cual.
            }
            $filas[$i] = ['nombre' => $nombre, 'campo' => $campo, 'valor' => $valor];
        }
        return $filas;
    }

    /**
     * Configuracion del selector de variables (amd local_reglasdeuda/variables).
     *
     * @return array
     */
    private static function config_variables(): array {
        $c = 'local_reglasdeuda';
        $grupos = [];
        foreach (plantilla::VARIABLES_FIJAS as $grupo => $nombres) {
            $grupos[] = [
                'nombre' => get_string('variables_' . $grupo, $c),
                'variables' => array_map(function($n) use ($c) {
                    return ['nombre' => $n, 'ayuda' => get_string('var_' . $n, $c)];
                }, $nombres),
            ];
        }
        return [
            'editor' => 'id_mensaje_editor',
            'campos' => ['id_titulo', 'id_botontexto', 'id_botonurl', 'id_botonurlrespaldo'],
            'ejemplo' => 'id_respuestaejemplo',
            'rutadatos' => 'id_rutadatos',
            'grupos' => $grupos,
            'textos' => [
                'api' => get_string('variables_api', $c),
                'sinapi' => get_string('variables_sinapi', $c),
                'copiado' => get_string('variables_copiado', $c),
            ],
        ];
    }

    /**
     * Modulos de actividad instalados.
     *
     * @return array modname => nombre
     */
    private static function modulos(): array {
        global $DB;
        $modulos = [];
        foreach ($DB->get_records('modules', ['visible' => 1], 'name', 'id, name') as $m) {
            $modulos[$m->name] = get_string('modulename', 'mod_' . $m->name);
        }
        \core_collator::asort($modulos);
        return $modulos;
    }

    /**
     * Roles del sitio.
     *
     * @return array id => nombre
     */
    private static function roles(): array {
        return role_fix_names(get_all_roles(), \context_system::instance(), ROLENAME_ORIGINAL, true);
    }
}
