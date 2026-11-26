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

namespace local_reglasdeuda;

/**
 * Observadores de eventos.
 *
 * @package    local_reglasdeuda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observador {

    /**
     * @param \core\event\user_deleted $evento
     */
    public static function usuario_borrado(\core\event\user_deleted $evento): void {
        local\evaluador::olvidar_usuario((int)$evento->objectid);
    }

    /**
     * Inicio de sesion con cualquier autenticacion.
     *
     * @param \core\event\user_loggedin $evento
     */
    public static function usuario_ingreso(\core\event\user_loggedin $evento): void {
        local\aplicador::al_ingresar();
    }

    /**
     * @param \core\event\course_module_deleted $evento
     */
    public static function actividad_borrada(\core\event\course_module_deleted $evento): void {
        global $DB;
        $DB->delete_records(local\recursos::TABLA, ['cmid' => (int)$evento->objectid]);
    }
}
