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
 * Crear o editar una regla.
 *
 * @package    local_reglasdeuda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_reglasdeuda\formularios\regla_form;
use local_reglasdeuda\local\parametros;
use local_reglasdeuda\local\pestanas;
use local_reglasdeuda\local\reglas;

require_once(__DIR__ . '/../../config.php');
// Carga explicita: no depende del mapa de clases en cache de Moodle ("Class not found" tras actualizar).
require_once(__DIR__ . '/classes/formularios/regla_form.php');

$id = optional_param('id', 0, PARAM_INT);

$url = new moodle_url('/local/reglasdeuda/editar.php', ['id' => $id]);
$titulo = get_string($id ? 'editarregla' : 'nuevaregla', 'local_reglasdeuda');
pestanas::configurar_pagina($url, $titulo);
$listado = new moodle_url('/local/reglasdeuda/index.php');

$regla = $id ? reglas::obtener($id) : reglas::por_defecto();
if (!$regla) {
    throw new moodle_exception('invalidrecord', 'error', $listado);
}

// Registro => formulario: CSV a listas, parametros a filas y mensaje al formato del editor.
$datos = clone $regla;
$datos->globalmodulos = reglas::csv($regla->globalmodulos);
$datos->globalcategorias = reglas::csv($regla->globalcategorias);
$datos->globalcursos = reglas::csv($regla->globalcursos);
$datos->rolesaplica = reglas::csv($regla->rolesaplica);
$datos->mensaje_editor = ['text' => (string)$regla->mensaje, 'format' => (int)$regla->mensajeformat];
$lista = parametros::de_regla($regla);
foreach ($lista as $i => $p) {
    $datos->param_nombre[$i] = $p['nombre'];
    $datos->param_campo[$i] = $p['campo'];
    $datos->param_valor[$i] = $p['valor'];
}

// Una fila vacia extra para agregar un parametro sin pulsar el boton.
$form = new regla_form($url, ['numparametros' => count($lista) + 1]);
$form->set_data($datos);

if ($form->is_cancelled()) {
    redirect($listado);
}
if ($enviado = $form->get_data()) {
    $guardar = new stdClass();
    foreach (array_keys((array)reglas::por_defecto()) as $campo) {
        if (property_exists($enviado, $campo)) {
            $guardar->$campo = $enviado->$campo;
        }
    }
    $guardar->id = $id;
    $guardar->parametros = json_encode(array_values(regla_form::parametros_enviados($enviado)), JSON_UNESCAPED_UNICODE);
    $guardar->globalmodulos = implode(',', $enviado->globalmodulos ?? []);
    $guardar->globalcategorias = implode(',', array_map('intval', $enviado->globalcategorias ?? []));
    $guardar->globalcursos = implode(',', array_map('intval', (array)($enviado->globalcursos ?? [])));
    $guardar->actividades = implode(',', array_unique(array_map('intval', reglas::csv($enviado->actividades ?? ''))));
    $guardar->rolesaplica = implode(',', array_map('intval', $enviado->rolesaplica ?? []));
    $guardar->mensaje = $enviado->mensaje_editor['text'];
    $guardar->mensajeformat = (int)$enviado->mensaje_editor['format'];
    if ($guardar->accion !== reglas::ACCION_RESTRINGIR) {
        $guardar->globalactivo = 0;
        $guardar->actividades = '';
    }
    $nuevoid = reglas::guardar($guardar);
    redirect(new moodle_url('/local/reglasdeuda/probar.php', ['id' => $nuevoid]),
        get_string('reglaguardada', 'local_reglasdeuda'), null, \core\output\notification::NOTIFY_SUCCESS);
}

// Selector de variables (la configuracion viaja en data-config del contenedor, ver regla_form).
$PAGE->requires->js_call_amd('local_reglasdeuda/variables', 'init', ['local-reglasdeuda-variables']);

echo $OUTPUT->header();
echo pestanas::html($id ? 'reglas' : 'nueva');
echo $OUTPUT->heading($titulo, 3);
if ($id) {
    echo html_writer::div(html_writer::link(new moodle_url('/local/reglasdeuda/probar.php', ['id' => $id]),
        get_string('probar', 'local_reglasdeuda'), ['class' => 'btn btn-secondary']), 'mb-3');
}
$form->display();
echo $OUTPUT->footer();
