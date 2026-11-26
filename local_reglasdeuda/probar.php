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
 * Probar una regla: consulta real a la API con un usuario, sin aplicar ninguna accion.
 *
 * @package    local_reglasdeuda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_reglasdeuda\local\evaluador;
use local_reglasdeuda\local\mensaje;
use local_reglasdeuda\local\pestanas;
use local_reglasdeuda\local\reglas;

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);

$url = new moodle_url('/local/reglasdeuda/probar.php', ['id' => $id]);
pestanas::configurar_pagina($url, get_string('probar', 'local_reglasdeuda'));

$regla = reglas::obtener($id);
if (!$regla) {
    throw new moodle_exception('invalidrecord', 'error', new moodle_url('/local/reglasdeuda/index.php'));
}
$c = 'local_reglasdeuda';

// "Usar esta respuesta como ejemplo": la ultima respuesta queda en la sesion del admin.
if (optional_param('usarejemplo', 0, PARAM_BOOL) && confirm_sesskey()) {
    $ultima = $SESSION->local_reglasdeuda_ultima[$id] ?? '';
    if ($ultima !== '' && is_array(json_decode($ultima, true))) {
        // set_field y no guardar(): no cambia timemodified, asi no invalida los resultados cacheados de todos.
        $DB->set_field(reglas::TABLA, 'respuestaejemplo', json_encode(json_decode($ultima), JSON_PRETTY_PRINT |
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ['id' => $id]);
        redirect(new moodle_url('/local/reglasdeuda/editar.php', ['id' => $id]), get_string('ejemploguardado', $c),
            null, \core\output\notification::NOTIFY_SUCCESS);
    }
}

// Formulario simple (sin moodleform): usuario a probar.
$textousuario = trim(optional_param('usuario', '', PARAM_RAW_TRIMMED));
$usuario = null;
$errorusuario = '';
if ($textousuario !== '' && confirm_sesskey()) {
    $usuario = local_reglasdeuda_buscar_usuario($textousuario);
    if (!$usuario) {
        $errorusuario = get_string('probar_nousuario', $c);
    }
}

/**
 * Busca un usuario por nombre de usuario, correo o numero de ID (el primero que coincida).
 *
 * @param string $texto
 * @return stdClass|null
 */
function local_reglasdeuda_buscar_usuario(string $texto): ?stdClass {
    global $DB;
    $usuario = core_user::get_user_by_username(core_text::strtolower($texto));
    if (!$usuario) {
        $usuario = core_user::get_user_by_email($texto);
    }
    if (!$usuario) {
        $encontrados = $DB->get_records('user', ['idnumber' => $texto, 'deleted' => 0], 'id ASC', '*', 0, 1);
        $usuario = $encontrados ? reset($encontrados) : false;
    }
    return ($usuario && empty($usuario->deleted)) ? $usuario : null;
}

echo $OUTPUT->header();
echo pestanas::html('reglas');
echo $OUTPUT->heading(get_string('probarregla', $c, format_string($regla->nombre)), 3);
echo html_writer::div(
    html_writer::link(new moodle_url('/local/reglasdeuda/editar.php', ['id' => $id]), get_string('edit')) . ' · ' .
    html_writer::link(new moodle_url('/local/reglasdeuda/index.php'), get_string('reglas', $c)), 'mb-3');
echo $OUTPUT->notification(get_string('probar_aviso', $c), 'info');
echo html_writer::start_tag('form', ['method' => 'post', 'action' => $url->out(false), 'class' => 'form-inline mb-3']);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
echo html_writer::tag('label', get_string('probar_usuario', $c), ['for' => 'id_usuario', 'class' => 'me-2 mr-2']);
echo html_writer::empty_tag('input', ['type' => 'text', 'name' => 'usuario', 'id' => 'id_usuario', 'size' => 30,
    'class' => 'form-control me-2 mr-2', 'value' => $textousuario,
    'placeholder' => get_string('probar_usuario_ph', $c)]);
echo html_writer::empty_tag('input', ['type' => 'submit', 'class' => 'btn btn-primary',
    'value' => get_string('probar', $c)]);
echo html_writer::end_tag('form');
if ($errorusuario !== '') {
    echo $OUTPUT->notification($errorusuario, 'error');
}

if ($usuario) {
    $ev = evaluador::evaluar($regla, $usuario, [], false);
    $SESSION->local_reglasdeuda_ultima[$id] = $ev['respuesta'];

    $bloque = function(string $titulo, string $contenido) {
        return html_writer::tag('h5', $titulo, ['class' => 'mt-3']) . $contenido;
    };
    $codigo = function(string $texto) {
        $json = json_decode($texto);
        if ($json !== null) {
            $texto = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        return html_writer::tag('pre', s($texto), ['class' => 'bg-light p-2 border rounded small']);
    };

    echo html_writer::tag('h4', get_string('resultadoprueba', $c, fullname($usuario)), ['class' => 'mt-4']);
    if (evaluador::esta_exento($regla, (int)$usuario->id)) {
        echo $OUTPUT->notification(get_string('probar_exento', $c), 'warning');
    }

    // La peticion ya viene descrita (metodo, URL, cabeceras con el token oculto y cuerpo).
    echo $bloque(get_string('peticion', $c), html_writer::tag('pre', s($ev['peticion']),
        ['class' => 'bg-light p-2 border rounded small']));

    $estado = 'HTTP ' . $ev['http'] . ' · ' . $ev['duracionms'] . ' ms';
    echo $bloque(get_string('respuesta', $c), html_writer::div($estado, 'small mb-1') .
        ($ev['respuesta'] !== '' ? $codigo($ev['respuesta']) : ''));

    if ($ev['error'] !== '') {
        echo $OUTPUT->notification(get_string('probar_error', $c, s($ev['error'])), 'error');
    }

    $acciones = reglas::opciones_accion();
    $decision = $ev['aplica']
        ? get_string('decision_aplica', $c, $acciones[$regla->accion] ?? $regla->accion)
        : get_string('decision_noaplica', $c);
    $resultado = $ev['resultado'] === null ? '—' : ($ev['resultado'] ? 'true' : 'false');
    echo $bloque(get_string('decision', $c), html_writer::div(
        get_string('decision_detalle', $c, ['resultado' => $resultado, 'valoractiva' => $regla->valoractiva ? 'true' : 'false']) .
        '<br>' . html_writer::tag('strong', $decision), $ev['aplica'] ? 'text-danger' : 'text-success'));

    if ($ev['datos']) {
        $lista = array_map(function($k, $v) {
            return html_writer::tag('code', '{' . s($k) . '}') . ' = ' . s($v);
        }, array_keys($ev['datos']), $ev['datos']);
        echo $bloque(get_string('variablesrecibidas', $c), html_writer::alist($lista, ['class' => 'small']));
    }

    echo $bloque(get_string('vistaprevia', $c),
        mensaje::html(mensaje::contexto($regla, $ev, $usuario, [], false)));

    if ($ev['error'] === '' && $ev['respuesta'] !== '') {
        echo $OUTPUT->single_button(new moodle_url($url, ['usarejemplo' => 1]), get_string('usarejemplo', $c), 'post');
    }
}

echo $OUTPUT->footer();
