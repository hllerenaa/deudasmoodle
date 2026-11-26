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

use local_reglasdeuda\local\archivos;
use local_reglasdeuda\local\pestanas;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');
// El formulario va dentro de esta pagina y no en un archivo aparte: el antivirus del hosting
// borraba los archivos de formularios sueltos (classes/form/, classes/formularios/).

/**
 * Ajustes generales del plugin (pestana Ajustes).
 *
 * @package    local_reglasdeuda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_reglasdeuda_editor_ajustes extends \moodleform {

    protected function definition() {
        $f = $this->_form;
        $c = 'local_reglasdeuda';
        $f->addElement('advcheckbox', 'habilitado', get_string('habilitado', $c));
        $f->addElement('static', 'habilitado_desc', '', get_string('habilitado_desc', $c));
        $f->addElement('advcheckbox', 'hostsinternos', get_string('hostsinternos', $c));
        $f->addElement('static', 'hostsinternos_desc', '', get_string('hostsinternos_desc', $c));
        $this->add_action_buttons(false);
    }
}


$url = new moodle_url('/local/reglasdeuda/ajustes.php');
pestanas::configurar_pagina($url, get_string('ajustesgenerales', 'local_reglasdeuda'));
// "Hosts internos" abre peticiones a la red privada (SSRF): solo quien puede configurar el sitio.
require_capability('moodle/site:config', context_system::instance());

$form = new local_reglasdeuda_editor_ajustes($url);
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
// Verificacion de archivos: detecta lo que el antivirus del hosting haya borrado o modificado.
echo $OUTPUT->heading(get_string('verificacion', 'local_reglasdeuda'), 4);
$problemas = archivos::aviso(true);
echo $problemas !== '' ? $problemas : $OUTPUT->notification(get_string('verificacion_ok', 'local_reglasdeuda'), 'success');
$form->display();
echo $OUTPUT->footer();
