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

/**
 * Callbacks de Moodle.
 *
 * @package    local_reglasdeuda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Se ejecuta al final de cada require_login (Moodle 4.1 a 5.1; no existe hook que lo reemplace).
 *
 * @param mixed $courseorid
 * @param bool $autologinguest
 * @param cm_info|stdClass|null $cm
 * @param bool $setwantsurltome
 * @param bool $preventredirect
 */
function local_reglasdeuda_after_require_login($courseorid = null, $autologinguest = true, $cm = null,
        $setwantsurltome = true, $preventredirect = false) {
    try {
        \local_reglasdeuda\local\aplicador::despues_de_login($courseorid, $cm, (bool)$preventredirect);
    } catch (\moodle_exception $e) {
        if ($e->module === 'local_reglasdeuda') {
            throw $e; // Bloqueo intencional cuando no se puede redirigir.
        }
        error_log('local_reglasdeuda: ' . $e->getMessage());
    } catch (\Throwable $e) {
        // Un fallo del plugin nunca debe impedir el acceso a la plataforma.
        error_log('local_reglasdeuda: ' . $e->getMessage());
    }
}
