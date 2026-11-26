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

namespace availability_reglasdeuda;

use local_reglasdeuda\local\reglas;

/**
 * Formulario "Restringir acceso": selector de la regla.
 *
 * @package    availability_reglasdeuda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class frontend extends \core_availability\frontend {

    protected function get_javascript_strings() {
        return ['title', 'error_seleccionar'];
    }

    protected function get_javascript_init_params($course, ?\cm_info $cm = null, ?\section_info $section = null) {
        global $DB;
        $opciones = [];
        $todas = $DB->get_records(reglas::TABLA, ['accion' => reglas::ACCION_RESTRINGIR], 'orden ASC, id ASC',
            'id, nombre, activa');
        foreach ($todas as $regla) {
            $nombre = format_string($regla->nombre);
            if (!$regla->activa) {
                $nombre .= ' ' . get_string('inactiva', 'availability_reglasdeuda');
            }
            $opciones[] = ['id' => (int)$regla->id, 'nombre' => $nombre];
        }
        return [$opciones];
    }

    protected function allow_add($course, ?\cm_info $cm = null, ?\section_info $section = null) {
        return (bool)reglas::activas_por_accion(reglas::ACCION_RESTRINGIR);
    }
}
