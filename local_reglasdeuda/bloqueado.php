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
 * Pagina que ve el usuario bloqueado (plataforma o actividad).
 *
 * @package    local_reglasdeuda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_reglasdeuda\local\evaluador;
use local_reglasdeuda\local\mensaje;
use local_reglasdeuda\local\reglas;

require_once(__DIR__ . '/../../config.php');

$reglaid = required_param('regla', PARAM_INT);
$cmid = optional_param('cmid', 0, PARAM_INT);
$cursoid = optional_param('curso', 0, PARAM_INT); // Restriccion de una seccion (sin actividad).

require_login(null, false);

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/reglasdeuda/bloqueado.php',
    ['regla' => $reglaid, 'cmid' => $cmid, 'curso' => $cursoid]));

$curso = null;
$volver = new moodle_url('/');
$destino = $volver; // A donde ir si la regla ya no aplica.
if ($cmid) {
    [$curso, $cm] = get_course_and_cm_from_cmid($cmid);
    $destino = new moodle_url('/mod/' . $cm->modname . '/view.php', ['id' => $cmid]);
} else if ($cursoid && $cursoid != SITEID) {
    $curso = get_course($cursoid);
}
if ($curso) {
    if (!can_access_course($curso)) {
        redirect(new moodle_url('/'));
    }
    $volver = new moodle_url('/course/view.php', ['id' => $curso->id]);
    $destino = $cmid ? $destino : $volver;
}
$encurso = (bool)$curso;

// Si la regla ya no aplica (pago realizado, regla desactivada, usuario exento) se vuelve a donde estaba.
$regla = reglas::obtener($reglaid);
if (!$regla || !$regla->activa || evaluador::esta_exento($regla, (int)$USER->id)) {
    redirect($destino);
}
// La pagina que cierra la sesion es solo para "Bloquear acceso a la plataforma". Con "Solo mostrar
// mensaje" (o si la regla cambio de accion) se deja pasar: nunca se cierra la sesion.
if (!$curso && $regla->accion !== reglas::ACCION_BLOQUEAR) {
    redirect($destino);
}
$contexto = ['curso' => $curso, 'cmid' => $cmid ?: null];
$evaluacion = evaluador::evaluar($regla, $USER, $contexto);
if (!$evaluacion['aplica']) {
    redirect($destino);
}

// Dentro del curso/actividad: migas de pan, menu del curso y nombre del curso arriba, para que se
// entienda que actividad esta bloqueada.
$titulo = get_string('accesorestringido', 'local_reglasdeuda');
$explicacion = '';
if ($cmid) {
    $PAGE->set_cm($cm, $curso);
    $PAGE->set_pagelayout('incourse');
    // La cabecera propia de Moodle muestra el nombre y la descripcion de la actividad.
    $PAGE->activityheader->set_attrs(['hidecompletion' => true]);
    $nombre = format_string($cm->name, true, ['context' => $PAGE->context]);
    $titulo = $nombre;
    $explicacion = get_string('actividadbloqueada_desc', 'local_reglasdeuda', $nombre);
    $PAGE->set_title($nombre . ' | ' . format_string($curso->fullname));
    $PAGE->set_heading(format_string($curso->fullname));
} else if ($encurso) {
    $PAGE->set_course($curso);
    $PAGE->set_pagelayout('incourse');
    $explicacion = get_string('cursobloqueado_desc', 'local_reglasdeuda', format_string($curso->fullname));
    $PAGE->set_title($titulo . ' | ' . format_string($curso->fullname));
    $PAGE->set_heading(format_string($curso->fullname));
} else {
    // Bloqueo de plataforma: se arma el mensaje, se cierra la sesion y se explica por que.
    $mensajeplataforma = mensaje::html(mensaje::contexto($regla, $evaluacion, $USER, $contexto, false));
    evaluador::olvidar_usuario((int)$USER->id); // Al volver a ingresar se consulta de nuevo la API.
    require_logout();
    $SESSION->local_reglasdeuda_sesioncerrada = true;
    // Al volver a ingresar, ir a la pagina de inicio y no de vuelta a esta pagina (Moodle usaria la
    // pagina anterior como destino); si sigue con deuda, el plugin lo vuelve a revisar al ingresar.
    $SESSION->wantsurl = $CFG->wwwroot . '/';
    $PAGE->set_context(context_system::instance());
    $PAGE->set_pagelayout('login');
    $titulo = get_string('sesioncerrada', 'local_reglasdeuda');
    $PAGE->set_title($titulo);
    $PAGE->set_heading($SITE->fullname);
    echo $OUTPUT->header();
    echo $OUTPUT->heading('🔒 ' . $titulo, 2);
    echo html_writer::tag('p', get_string('sesioncerrada_desc', 'local_reglasdeuda'), ['class' => 'lead']);
    echo $mensajeplataforma;
    echo html_writer::div(html_writer::link(new moodle_url('/login/index.php'),
        get_string('volveringresar', 'local_reglasdeuda'), ['class' => 'btn btn-outline-secondary']), 'mt-3');
    echo $OUTPUT->footer();
    exit;
}

echo $OUTPUT->header();
if ($encurso) {
    // Algunos temas (Boost) no muestran el nombre del curso en las paginas de actividad.
    echo html_writer::div(get_string('course') . ': ' . html_writer::link($volver,
        format_string($curso->fullname, true, ['context' => context_course::instance($curso->id)])), 'mb-1');
}
if ($cmid) {
    echo html_writer::div(html_writer::span('🔒 ' . get_string('actividadbloqueada', 'local_reglasdeuda'),
        'badge bg-danger badge-danger', ['style' => 'font-size:.95rem;padding:.4em .7em']), 'mb-2');
}
if ($explicacion !== '') {
    echo html_writer::tag('p', $explicacion, ['class' => 'lead']);
}
echo mensaje::html(mensaje::contexto($regla, $evaluacion, $USER, $contexto));

echo html_writer::div(html_writer::link($volver, get_string('volvercurso', 'local_reglasdeuda'),
    ['class' => 'btn btn-outline-secondary']), 'mt-3');

echo $OUTPUT->footer();
