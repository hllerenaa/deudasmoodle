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
 * Diagnostico: recorre, para el usuario actual, cada paso que da el plugin antes de mostrar un aviso
 * y dice en cual se corta.
 *
 * @package    local_reglasdeuda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_reglasdeuda\local\archivos;
use local_reglasdeuda\local\evaluador;
use local_reglasdeuda\local\mensaje;
use local_reglasdeuda\local\pestanas;
use local_reglasdeuda\local\reglas;

require_once(__DIR__ . '/../../config.php');

$url = new moodle_url('/local/reglasdeuda/diagnostico.php');
pestanas::configurar_pagina($url, get_string('diagnostico', 'local_reglasdeuda'));

$filas = [];
$paso = function(string $que, bool $ok, string $detalle = '') use (&$filas) {
    $filas[] = [$ok ? '✅' : '❌', s($que), $detalle];
};

$plugin = core_plugin_manager::instance()->get_plugin_info('local_reglasdeuda');
$paso('Version del plugin', $plugin->versiondisk == $plugin->versiondb,
    'disco ' . $plugin->versiondisk . ' / base de datos ' . $plugin->versiondb . ' (' . s($plugin->release) . ')');
$verificacion = archivos::verificar();
$paso('Archivos del plugin completos', !$verificacion['faltan'] && !$verificacion['cambiados'],
    s(implode(', ', array_merge($verificacion['faltan'], $verificacion['cambiados']))));
$paso('Aplicar reglas (pestana Ajustes)', (bool)get_config('local_reglasdeuda', 'habilitado'));

// Hook que muestra los avisos (Moodle 4.4+) o callback de lib.php (4.1-4.3).
$hookok = false;
$hookdetalle = '';
if (class_exists('\core\hook\manager') && method_exists('\core\hook\manager', 'get_callbacks_for_hook')) {
    $callbacks = \core\hook\manager::get_instance()->get_callbacks_for_hook('core\hook\output\before_standard_top_of_body_html_generation');
    foreach ($callbacks as $cb) {
        $hookdetalle .= s($cb['callback']) . ($cb['disabled'] ?? false ? ' (DESACTIVADO)' : '') . '<br>';
        if (strpos($cb['callback'], 'local_reglasdeuda') !== false && empty($cb['disabled'])) {
            $hookok = true;
        }
    }
} else {
    $hookok = function_exists('local_reglasdeuda_before_standard_top_of_body_html');
    $hookdetalle = 'Moodle sin hooks: callback de lib.php';
}
$paso('Gancho de avisos registrado (inicio del body)', $hookok, $hookdetalle);
$paso('Tema', true, s($PAGE->theme->name) . ' (el aviso se inserta al inicio del contenido; no depende del tema)');

$avisos = reglas::activas_por_accion(reglas::ACCION_AVISAR);
$paso('Reglas activas "Solo mostrar mensaje"', (bool)$avisos, count($avisos) . ' regla(s)');

$mensajes = '';
foreach ($avisos as $regla) {
    $nombre = format_string($regla->nombre);
    $exento = evaluador::esta_exento($regla, (int)$USER->id);
    $paso("[$nombre] Se aplica a su usuario", !$exento, $exento ? 'exento' : 'no exento');
    try {
        $ev = evaluador::evaluar($regla, $USER, [], true);
        $paso("[$nombre] Consulta a la API (con cache, como en las paginas)", $ev['error'] === '',
            'HTTP ' . $ev['http'] . ($ev['cache'] ? ' (respuesta en cache)' : '') . ($ev['error'] !== '' ? ' — ' . s($ev['error']) : ''));
        $paso("[$nombre] La regla se activa para usted", $ev['aplica'],
            'resultado=' . var_export($ev['resultado'], true) . ', actua con ' . ($regla->valoractiva ? 'true' : 'false'));
        if ($ev['aplica'] && !$exento) {
            $c = mensaje::contexto($regla, $ev, $USER, []);
            $mensajes .= mensaje::html($c);
        }
    } catch (\Throwable $e) {
        $paso("[$nombre] Error interno", false, s(get_class($e) . ': ' . $e->getMessage()));
    }
}

echo $OUTPUT->header();
echo pestanas::html('diagnostico');
echo $OUTPUT->heading(get_string('diagnostico', 'local_reglasdeuda'), 3);
echo html_writer::div(get_string('diagnostico_desc', 'local_reglasdeuda'), 'mb-3');
$tabla = new html_table();
$tabla->head = ['', get_string('diagnostico_paso', 'local_reglasdeuda'), get_string('diagnostico_detalle', 'local_reglasdeuda')];
$tabla->data = $filas;
$tabla->attributes['class'] = 'generaltable table-sm';
echo html_writer::table($tabla);
if ($mensajes !== '') {
    echo $OUTPUT->heading(get_string('vistaprevia', 'local_reglasdeuda'), 4);
    echo $mensajes;
}
echo $OUTPUT->footer();
