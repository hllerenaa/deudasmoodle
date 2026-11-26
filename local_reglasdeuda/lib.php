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
 * Callbacks de Moodle.
 *
 * @package    local_reglasdeuda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Sirve la imagen del mensaje (area "imagen" del contexto de sistema) a usuarios identificados.
 *
 * @param stdClass $course
 * @param stdClass|null $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function local_reglasdeuda_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    if ($context->contextlevel != CONTEXT_SYSTEM || $filearea !== 'imagen') {
        return false;
    }
    require_login(null, false);
    $itemid = (int)array_shift($args);
    $nombre = array_pop($args);
    $ruta = $args ? '/' . implode('/', $args) . '/' : '/';
    $archivo = get_file_storage()->get_file($context->id, 'local_reglasdeuda', 'imagen', $itemid, $ruta, $nombre);
    if (!$archivo || $archivo->is_directory()) {
        return false;
    }
    send_stored_file($archivo, DAYSECS, 0, $forcedownload, $options);
}

/**
 * Agrega "Bloqueo por deuda" a la configuracion de cada actividad (si hay reglas con recursos).
 *
 * @param moodleform_mod $formwrapper
 * @param MoodleQuickForm $mform
 */
function local_reglasdeuda_coursemodule_standard_elements($formwrapper, $mform) {
    try {
        \local_reglasdeuda\local\recursos::agregar_campos($formwrapper, $mform);
    } catch (\Throwable $e) {
        error_log('local_reglasdeuda: ' . $e->getMessage());
    }
}

/**
 * Guarda "Bloqueo por deuda" al guardar la actividad.
 *
 * @param stdClass $data
 * @param stdClass $course
 * @return stdClass
 */
function local_reglasdeuda_coursemodule_edit_post_actions($data, $course) {
    \local_reglasdeuda\local\recursos::guardar($data);
    return $data;
}

/**
 * Avisos al inicio del body (Moodle 4.1 a 4.3). En 4.4+ lo reemplaza db/hooks.php
 * (Moodle ignora este callback si el plugin registra el hook).
 *
 * @return string
 */
function local_reglasdeuda_before_standard_top_of_body_html() {
    try {
        return \local_reglasdeuda\local\aplicador::html_avisos();
    } catch (\Throwable $e) {
        error_log('local_reglasdeuda: ' . $e->getMessage());
        return '';
    }
}

/**
 * Se ejecuta al final de cada require_login (Moodle 4.1 a 5.1; no existe hook que lo reemplace).
 *
 * @param mixed $courseorid
 * @param bool $autologinguest
 * @param cm_info|stdClass|null $cm
 * @param bool $setwantsurltome
 * @param bool $preventredirect
 */
function local_reglasdeuda_after_require_login($courseorid = null, $autologinguest = true, $cm = null,
        $setwantsurltome = true, $preventredirect = false) {
    try {
        \local_reglasdeuda\local\aplicador::despues_de_login($courseorid, $cm, (bool)$preventredirect);
    } catch (\moodle_exception $e) {
        if ($e->module === 'local_reglasdeuda') {
            throw $e; // Bloqueo intencional cuando no se puede redirigir.
        }
        error_log('local_reglasdeuda: ' . $e->getMessage());
    } catch (\Throwable $e) {
        // Un fallo del plugin nunca debe impedir el acceso a la plataforma.
        error_log('local_reglasdeuda: ' . $e->getMessage());
    }
}
