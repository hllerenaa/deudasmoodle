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
 * Callbacks de hooks (Moodle 4.4+).
 *
 * @package    local_reglasdeuda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {

    /**
     * Antes de enviar la cabecera de cada pagina: avisos de las reglas "solo mensaje".
     *
     * @param \core\hook\output\before_http_headers $hook
     */
    public static function antes_cabeceras($hook): void {
        try {
            local\aplicador::avisos_pagina();
        } catch (\Throwable $e) {
            error_log('local_reglasdeuda: ' . $e->getMessage());
        }
    }
}
