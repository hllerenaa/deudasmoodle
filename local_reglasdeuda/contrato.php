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
 * Contrato que debe cumplir la API: para entregar al equipo que la desarrolla.
 *
 * @package    local_reglasdeuda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_reglasdeuda\local\plantilla;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('local_reglasdeuda_contrato');
$c = 'local_reglasdeuda';

$pre = function(string $texto) {
    return html_writer::tag('pre', s($texto), ['class' => 'bg-light p-3 border rounded']);
};
$respuesta = json_encode([
    'resultado' => true,
    'mensaje' => '',
    'datos' => ['total' => '235.50', 'url_pago' => 'https://pagos.example.edu/?ref=0912345678'],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$ejemplo = plantilla::construir_cuerpo(plantilla::CUERPO_POR_DEFECTO, [
    'regla' => 'Deuda', 'identificador' => '0912345678', 'tipo_identificador' => 'idnumber',
    'username' => 'jperez', 'email' => 'jperez@example.edu', 'idnumber' => '0912345678',
    'course_shortname' => 'MAT101', 'cmid' => '42',
]);
$ejemplo = json_encode(json_decode($ejemplo), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('contrato', $c));
echo html_writer::div(get_string('contrato_intro', $c), 'mb-3');

echo $OUTPUT->heading(get_string('contrato_llamada', $c), 4);
echo html_writer::div(get_string('contrato_llamada_desc', $c));

echo $OUTPUT->heading(get_string('contrato_peticion', $c), 4);
echo html_writer::div(get_string('contrato_peticion_desc', $c));
echo $pre($ejemplo);

echo $OUTPUT->heading(get_string('contrato_respuesta', $c), 4);
echo $pre($respuesta);
echo html_writer::div(get_string('contrato_respuesta_desc', $c));

echo $OUTPUT->heading(get_string('contrato_errores', $c), 4);
echo html_writer::div(get_string('contrato_errores_desc', $c));

echo $OUTPUT->footer();
