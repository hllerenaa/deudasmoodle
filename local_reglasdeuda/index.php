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
 * Listado de reglas: activar, editar, probar, borrar.
 *
 * @package    local_reglasdeuda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_reglasdeuda\local\pestanas;
use local_reglasdeuda\local\reglas;

require_once(__DIR__ . '/../../config.php');

$accion = optional_param('accion', '', PARAM_ALPHA);
$id = optional_param('id', 0, PARAM_INT);

$url = new moodle_url('/local/reglasdeuda/index.php');
pestanas::configurar_pagina($url, get_string('pluginname', 'local_reglasdeuda'));

if ($accion && $id && ($regla = reglas::obtener($id))) {
    if ($accion === 'activar' || $accion === 'desactivar') {
        require_sesskey();
        $regla->activa = $accion === 'activar' ? 1 : 0;
        reglas::guardar($regla);
        redirect($url);
    }
    if ($accion === 'borrar') {
        if (optional_param('confirmar', 0, PARAM_BOOL) && confirm_sesskey()) {
            reglas::borrar($id);
            redirect($url, get_string('reglaborrada', 'local_reglasdeuda'), null, \core\output\notification::NOTIFY_SUCCESS);
        }
        echo $OUTPUT->header();
        echo $OUTPUT->confirm(get_string('confirmarborrar', 'local_reglasdeuda', format_string($regla->nombre)),
            new moodle_url($url, ['accion' => 'borrar', 'id' => $id, 'confirmar' => 1, 'sesskey' => sesskey()]), $url);
        echo $OUTPUT->footer();
        die;
    }
}

echo $OUTPUT->header();
echo pestanas::html('reglas');
echo \local_reglasdeuda\local\archivos::aviso();

if (!get_config('local_reglasdeuda', 'habilitado')) {
    echo $OUTPUT->notification(get_string('plugindeshabilitado', 'local_reglasdeuda'), 'warning');
}

$acciones = reglas::opciones_accion();
$tabla = new html_table();
$tabla->head = [
    get_string('nombre', 'local_reglasdeuda'),
    get_string('accion', 'local_reglasdeuda'),
    get_string('seactivacuando', 'local_reglasdeuda'),
    get_string('url', 'local_reglasdeuda'),
    get_string('estado', 'local_reglasdeuda'),
    get_string('actions'),
];
$tabla->attributes['class'] = 'generaltable table-sm';

foreach (reglas::todas() as $regla) {
    $alternar = new moodle_url($url, ['accion' => $regla->activa ? 'desactivar' : 'activar', 'id' => $regla->id,
        'sesskey' => sesskey()]);
    $enlaces = [
        html_writer::link(new moodle_url('/local/reglasdeuda/editar.php', ['id' => $regla->id]), get_string('edit')),
        html_writer::link(new moodle_url('/local/reglasdeuda/probar.php', ['id' => $regla->id]),
            get_string('probar', 'local_reglasdeuda')),
        html_writer::link($alternar, get_string($regla->activa ? 'desactivar' : 'activar', 'local_reglasdeuda')),
        html_writer::link(new moodle_url($url, ['accion' => 'borrar', 'id' => $regla->id]), get_string('delete'),
            ['class' => 'text-danger']),
    ];
    $accion = $acciones[$regla->accion] ?? $regla->accion;
    if (!empty($regla->recursos)) {
        $partes = [get_string('recursos_corto', 'local_reglasdeuda')];
        if ($n = count(reglas::csv($regla->actividades))) {
            $partes[] = get_string('nactividades', 'local_reglasdeuda', $n);
        }
        if ($regla->globalactivo) {
            $partes[] = get_string('global', 'local_reglasdeuda');
        }
        $accion .= ' + ' . implode(', ', $partes);
    }
    $tabla->data[] = [
        format_string($regla->nombre),
        $accion,
        $regla->valoractiva ? 'true' : 'false',
        s(shorten_text($regla->url, 60)),
        $regla->activa
            ? html_writer::span(get_string('activa', 'local_reglasdeuda'), 'badge bg-success badge-success')
            : html_writer::span(get_string('inactiva', 'local_reglasdeuda'), 'badge bg-secondary badge-secondary'),
        implode(' · ', $enlaces),
    ];
}

if (empty($tabla->data)) {
    echo $OUTPUT->notification(get_string('sinreglas', 'local_reglasdeuda'), 'info');
} else {
    echo html_writer::table($tabla);
}


echo $OUTPUT->footer();
