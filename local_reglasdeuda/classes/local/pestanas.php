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
 * Pestanas de la administracion del plugin: todo en una sola pagina.
 *
 * @package    local_reglasdeuda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class pestanas {

    /**
     * Pagina de administracion comun: externalpage, URL y titulo.
     *
     * @param \moodle_url $url
     * @param string $titulo
     */
    public static function configurar_pagina(\moodle_url $url, string $titulo): void {
        global $CFG, $PAGE;
        require_once($CFG->libdir . '/adminlib.php');
        admin_externalpage_setup('local_reglasdeuda_reglas', '', null, $url);
        $PAGE->set_title($titulo);
    }

    /**
     * HTML del encabezado con las pestanas.
     *
     * @param string $actual reglas, nueva, contrato o ajustes
     * @return string
     */
    public static function html(string $actual): string {
        global $OUTPUT;
        $c = 'local_reglasdeuda';
        $pestanas = [
            new \tabobject('reglas', new \moodle_url('/local/reglasdeuda/index.php'), get_string('reglas', $c)),
            new \tabobject('nueva', new \moodle_url('/local/reglasdeuda/editar.php'), get_string('nuevaregla', $c)),
            new \tabobject('contrato', new \moodle_url('/local/reglasdeuda/contrato.php'), get_string('contrato', $c)),
        ];
        if (has_capability('moodle/site:config', \context_system::instance())) {
            $pestanas[] = new \tabobject('ajustes', new \moodle_url('/local/reglasdeuda/ajustes.php'),
                get_string('ajustesgenerales', $c));
        }
        return $OUTPUT->heading(get_string('pluginname', $c)) . $OUTPUT->tabtree($pestanas, $actual);
    }
}
