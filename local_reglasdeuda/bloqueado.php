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
$contexto = ['curso' => $curso, 'cmid' => $cmid ?: null];
$evaluacion = evaluador::evaluar($regla, $USER, $contexto);
if (!$evaluacion['aplica']) {
    redirect($destino);
}

$PAGE->set_pagelayout($encurso ? 'standard' : 'login');
$PAGE->set_title(get_string('accesorestringido', 'local_reglasdeuda'));
$PAGE->set_heading($SITE->fullname);

echo $OUTPUT->header();
echo mensaje::html(mensaje::contexto($regla, $evaluacion, $USER, $contexto));

if ($encurso) {
    echo html_writer::div(html_writer::link($volver, get_string('volvercurso', 'local_reglasdeuda'),
        ['class' => 'btn btn-outline-secondary']), 'mt-3');
} else {
    $salir = new moodle_url('/login/logout.php', ['sesskey' => sesskey()]);
    echo html_writer::div(html_writer::link($salir, get_string('logout'), ['class' => 'btn btn-outline-secondary']), 'mt-3');
}

echo $OUTPUT->footer();
