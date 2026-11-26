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

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Probar una regla con un usuario real.
 *
 * @package    local_reglasdeuda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class probar_form extends \moodleform {

    protected function definition() {
        $f = $this->_form;
        $f->addElement('hidden', 'id');
        $f->setType('id', PARAM_INT);
        $grupo = [
            $f->createElement('text', 'usuario', '', ['size' => 30,
                'placeholder' => get_string('probar_usuario_ph', 'local_reglasdeuda')]),
            $f->createElement('submit', 'probar', get_string('probar', 'local_reglasdeuda')),
        ];
        $f->addGroup($grupo, 'g_usuario', get_string('probar_usuario', 'local_reglasdeuda'), ' ', false);
        $f->setType('usuario', PARAM_RAW_TRIMMED);
    }

    public function validation($data, $files) {
        $errores = parent::validation($data, $files);
        if (trim($data['usuario'] ?? '') === '') {
            $errores['g_usuario'] = get_string('required');
        } else if (!self::buscar_usuario($data['usuario'])) {
            $errores['g_usuario'] = get_string('probar_nousuario', 'local_reglasdeuda');
        }
        return $errores;
    }

    /**
     * Busca por username, email o idnumber (el primero que coincida).
     *
     * @param string $texto
     * @return \stdClass|null
     */
    public static function buscar_usuario(string $texto): ?\stdClass {
        global $DB, $CFG;
        $texto = trim($texto);
        foreach (['username', 'email', 'idnumber'] as $campo) {
            $condiciones = [$campo => $campo === 'username' ? \core_text::strtolower($texto) : $texto, 'deleted' => 0];
            if ($campo === 'username') {
                $condiciones['mnethostid'] = $CFG->mnet_localhost_id;
            }
            $usuarios = $DB->get_records('user', $condiciones, 'id ASC', '*', 0, 1);
            if ($usuarios) {
                return reset($usuarios);
            }
        }
        return null;
    }
}
