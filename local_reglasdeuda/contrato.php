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
 * Contrato que debe cumplir la API de cada regla: para entregar al equipo que la desarrolla.
 *
 * @package    local_reglasdeuda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_reglasdeuda\local\cliente_api;
use local_reglasdeuda\local\parametros;
use local_reglasdeuda\local\pestanas;
use local_reglasdeuda\local\reglas;

require_once(__DIR__ . '/../../config.php');

$c = 'local_reglasdeuda';
pestanas::configurar_pagina(new moodle_url('/local/reglasdeuda/contrato.php'), get_string('contrato', $c));

$pre = function(string $texto) {
    return html_writer::tag('pre', s($texto), ['class' => 'bg-light p-3 border rounded']);
};

// Usuario ficticio para el ejemplo; los campos de perfil se muestran como <nombre del campo>.
$ficticio = (object)['id' => 123, 'username' => 'jperez', 'idnumber' => '0912345678', 'email' => 'jperez@example.edu',
    'firstname' => 'Juan', 'lastname' => 'Perez'];
$campos = parametros::campos_disponibles();

echo $OUTPUT->header();
echo pestanas::html('contrato');
echo html_writer::div(get_string('contrato_intro', $c), 'mb-3');

echo $OUTPUT->heading(get_string('contrato_llamada', $c), 4);
echo html_writer::div(get_string('contrato_llamada_desc', $c));

echo $OUTPUT->heading(get_string('contrato_peticion', $c), 4);
echo html_writer::div(get_string('contrato_peticion_desc', $c));
$todas = reglas::todas();
if (!$todas) {
    echo $OUTPUT->notification(get_string('contrato_sinreglas', $c), 'info');
}
foreach ($todas as $regla) {
    echo $OUTPUT->heading(format_string($regla->nombre), 5, 'mt-3');
    $tabla = new html_table();
    $tabla->head = [get_string('param_nombre', $c), get_string('param_campo', $c)];
    $tabla->attributes['class'] = 'generaltable table-sm w-auto';
    $valores = [];
    foreach (parametros::de_regla($regla) as $p) {
        $etiqueta = $campos[$p['campo']] ?? $p['campo'];
        if ($p['campo'] === parametros::FIJO) {
            $etiqueta .= ': ' . $p['valor'];
            $valores[$p['nombre']] = $p['valor'];
        } else if (strpos($p['campo'], parametros::PERFIL) === 0) {
            $valores[$p['nombre']] = '<' . substr($p['campo'], strlen(parametros::PERFIL)) . '>';
        } else {
            $valores[$p['nombre']] = (string)($ficticio->{$p['campo']} ?? '');
        }
        $tabla->data[] = [html_writer::tag('code', s($p['nombre'])), s($etiqueta)];
    }
    echo html_writer::table($tabla);
    echo $pre(cliente_api::describir($regla, cliente_api::peticion($regla, $valores)));
}

echo $OUTPUT->heading(get_string('contrato_respuesta', $c), 4);
echo $pre(json_encode([
    'resultado' => true,
    'mensaje' => '',
    'datos' => ['total' => '235.50', 'url_pago' => 'https://pagos.example.edu/?ref=0912345678'],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
echo html_writer::div(get_string('contrato_respuesta_desc', $c));

echo $OUTPUT->heading(get_string('contrato_errores', $c), 4);
echo html_writer::div(get_string('contrato_errores_desc', $c));

echo $OUTPUT->footer();
