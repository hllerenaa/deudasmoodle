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
 * Valores por defecto al instalar (los ajustes viven en la pestana Ajustes, no en settings.php).
 *
 * @package    local_reglasdeuda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @return bool
 */
function xmldb_local_reglasdeuda_install() {
    set_config('habilitado', 1, 'local_reglasdeuda');
    set_config('hostsinternos', 0, 'local_reglasdeuda');
    // Al terminar, el administrador va directo a la pantalla del plugin (ver aplicador).
    set_config('irconfiguracion', 1, 'local_reglasdeuda');
    return true;
}
