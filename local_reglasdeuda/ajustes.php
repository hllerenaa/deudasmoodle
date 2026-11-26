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
 * Pestana Ajustes: interruptor general y APIs en la red interna.
 *
 * @package    local_reglasdeuda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_reglasdeuda\form\ajustes_form;
use local_reglasdeuda\local\pestanas;

require_once(__DIR__ . '/../../config.php');

$url = new moodle_url('/local/reglasdeuda/ajustes.php');
pestanas::configurar_pagina($url, get_string('ajustesgenerales', 'local_reglasdeuda'));
// "Hosts internos" abre peticiones a la red privada (SSRF): solo quien puede configurar el sitio.
require_capability('moodle/site:config', context_system::instance());

$form = new ajustes_form($url);
$form->set_data([
    'habilitado' => (int)get_config('local_reglasdeuda', 'habilitado'),
    'hostsinternos' => (int)get_config('local_reglasdeuda', 'hostsinternos'),
]);
if ($datos = $form->get_data()) {
    foreach (['habilitado', 'hostsinternos'] as $nombre) {
        add_to_config_log($nombre, get_config('local_reglasdeuda', $nombre), (int)$datos->$nombre, 'local_reglasdeuda');
        set_config($nombre, (int)$datos->$nombre, 'local_reglasdeuda');
    }
    redirect($url, get_string('changessaved'), null, \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();
echo pestanas::html('ajustes');
$form->display();
echo $OUTPUT->footer();
