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
// Cualquier usuario identificado puede ver su propio diagnostico (sin tokens ni datos de otros).
require_login(null, false);
$admin = has_capability('local/reglasdeuda:gestionar', context_system::instance());
if ($admin) {
    pestanas::configurar_pagina($url, get_string('diagnostico', 'local_reglasdeuda'));
} else {
    $PAGE->set_context(context_system::instance());
    $PAGE->set_url($url);
    $PAGE->set_pagelayout('standard');
    $PAGE->set_title(get_string('diagnostico', 'local_reglasdeuda'));
    $PAGE->set_heading(get_string('diagnostico', 'local_reglasdeuda'));
}

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
// El aviso se inserta en standard_top_of_body_html(); un tema que la reemplace sin llamar al padre lo pierde.
// $OUTPUT aun no esta inicializado aqui (es un "bootstrap_renderer"): se usa el renderer real de la pagina.
$renderer = $PAGE->get_renderer('core');
$declarada = (new ReflectionMethod($renderer, 'standard_top_of_body_html'))->getDeclaringClass()->getName();
$temapropio = !in_array($declarada, ['core_renderer', 'renderer_base', 'core\\output\\core_renderer',
    'core\\output\\renderer_base'], true);
$paso('Tema', true, s($PAGE->theme->name) . ' · renderer: ' . s(get_class($renderer)) .
    ($temapropio ? ' · ATENCION: el tema reemplaza standard_top_of_body_html() en ' . s($declarada) : ''));

// Prueba real del gancho: genera el aviso como en el Area personal y muestra cualquier error.
\local_reglasdeuda\local\aplicador::$forzar = true;
try {
    if (class_exists('\\core\\hook\\output\\before_standard_top_of_body_html_generation')) {
        $hook = new \core\hook\output\before_standard_top_of_body_html_generation($renderer, '');
        \core\di::get(\core\hook\manager::class)->dispatch($hook);
        $salida = $hook->get_output();
    } else {
        $salida = local_reglasdeuda_before_standard_top_of_body_html();
    }
    $paso('Prueba del gancho: genera el aviso', strpos($salida, 'local-reglasdeuda-avisos') !== false,
        strpos($salida, 'local-reglasdeuda-avisos') !== false ? 'el aviso se genera' : 'no genero aviso (ver pasos de la regla)');
} catch (\Throwable $e) {
    $paso('Prueba del gancho: genera el aviso', false, s(get_class($e) . ': ' . $e->getMessage() . ' @ ' .
        basename($e->getFile()) . ':' . $e->getLine()));
}
\local_reglasdeuda\local\aplicador::reiniciar();

$avisos = reglas::activas_por_accion(reglas::ACCION_AVISAR);
$paso('Reglas activas "Solo mostrar mensaje"', (bool)$avisos, count($avisos) . ' regla(s)');

$bloqueos = reglas::activas_por_accion(reglas::ACCION_BLOQUEAR);
$paso('Reglas activas "Bloquear acceso a la plataforma"', true, count($bloqueos) . ' regla(s)');

$mensajes = '';
foreach (array_merge($avisos, $bloqueos) as $regla) {
    $nombre = format_string($regla->nombre);
    $esbloqueo = $regla->accion === reglas::ACCION_BLOQUEAR;
    $exento = evaluador::esta_exento($regla, (int)$USER->id);
    if ($esbloqueo) {
        // Administradores y docentes nunca se bloquean: se dice exactamente por que.
        $paso("[$nombre] Bloqueo de plataforma para su usuario", !$exento, $exento
            ? 'NO se le bloquea: ' . s(evaluador::motivo_exencion((int)$USER->id)) .
              '. Pruebe con una cuenta que solo sea estudiante.'
            : 'se le aplica (no es administrador ni docente)');
    } else {
        $paso("[$nombre] Se aplica a su usuario", !$exento, $exento ? 'exento' : 'no exento');
    }
    try {
        $ev = evaluador::evaluar($regla, $USER, [], true);
        $paso("[$nombre] Consulta a la API (con cache, como en las paginas)", $ev['error'] === '',
            'HTTP ' . $ev['http'] . ($ev['cache'] ? ' (respuesta en cache)' : '') . ($ev['error'] !== '' ? ' — ' . s($ev['error']) : ''));
        $paso("[$nombre] La regla se activa para usted", $ev['aplica'],
            'resultado=' . var_export($ev['resultado'], true) . ', actua con ' . ($regla->valoractiva ? 'true' : 'false'));
        if ($esbloqueo && $ev['aplica'] && !$exento) {
            $paso("[$nombre] Resultado", false, 'al ingresar (o al abrir cualquier pagina) se le cerraria la sesion ' .
                'con el mensaje de deuda');
        }
        if ($ev['aplica'] && !$exento) {
            $c = mensaje::contexto($regla, $ev, $USER, [], !$esbloqueo);
            $mensajes .= mensaje::html($c);
        }
    } catch (\Throwable $e) {
        $paso("[$nombre] Error interno", false, s(get_class($e) . ': ' . $e->getMessage()));
    }
}

echo $OUTPUT->header();
if ($admin) {
    echo pestanas::html('diagnostico');
}
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
