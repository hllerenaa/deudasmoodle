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

namespace local_reglasdeuda\local;

/**
 * Bloqueo de recursos configurado por el docente en cada actividad (tarea, cuestionario...).
 *
 * La regla debe tener "Permitir bloquear recursos". En la configuracion de la actividad aparece
 * "Bloqueo por deuda": la regla y que se bloquea (toda la actividad o solo entregar/iniciar intento).
 *
 * @package    local_reglasdeuda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class recursos {

    const TABLA = 'local_reglasdeuda_cm';

    /** Bloquear toda la actividad. */
    const MODO_ENTRAR = 'entrar';

    /** Bloquear solo la accion: iniciar un intento (cuestionario) o agregar/enviar la entrega (tarea). */
    const MODO_ENTREGAR = 'entregar';

    /** Modulos con bloqueo "solo entregar/iniciar". */
    const MODULOS_ENTREGA = ['quiz', 'assign'];

    /** @var array memo por peticion cmid => registro|false */
    private static $memo = [];

    /**
     * Reglas activas que permiten bloquear recursos.
     *
     * @return \stdClass[]
     */
    public static function reglas(): array {
        return array_filter(reglas::activas(), function($r) {
            return !empty($r->recursos);
        });
    }

    /**
     * Configuracion de bloqueo de una actividad.
     *
     * @param int $cmid
     * @return \stdClass|null
     */
    public static function de_actividad(int $cmid): ?\stdClass {
        global $DB;
        if (!array_key_exists($cmid, self::$memo)) {
            self::$memo[$cmid] = $DB->get_record(self::TABLA, ['cmid' => $cmid]) ?: false;
        }
        return self::$memo[$cmid] ?: null;
    }

    /**
     * Campos "Bloqueo por deuda" en el formulario de la actividad.
     *
     * @param \moodleform_mod $formwrapper
     * @param \MoodleQuickForm $mform
     */
    public static function agregar_campos($formwrapper, $mform): void {
        $disponibles = self::reglas();
        if (!$disponibles) {
            return;
        }
        $c = 'local_reglasdeuda';
        $modname = $formwrapper->get_current()->modulename ?? '';
        $opciones = [0 => get_string('cm_nobloquear', $c)];
        foreach ($disponibles as $r) {
            $opciones[$r->id] = format_string($r->nombre);
        }
        $mform->addElement('header', 'reglasdeuda_cabecera', get_string('cm_titulo', $c));
        $mform->addElement('select', 'reglasdeuda_regla', get_string('cm_regla', $c), $opciones);
        $mform->addHelpButton('reglasdeuda_regla', 'cm_regla', $c);
        if (in_array($modname, self::MODULOS_ENTREGA, true)) {
            $mform->addElement('select', 'reglasdeuda_modo', get_string('cm_modo', $c), [
                self::MODO_ENTREGAR => get_string('cm_modo_entregar_' . $modname, $c),
                self::MODO_ENTRAR => get_string('cm_modo_entrar', $c),
            ]);
            $mform->hideIf('reglasdeuda_modo', 'reglasdeuda_regla', 'eq', 0);
        } else {
            $mform->addElement('hidden', 'reglasdeuda_modo', self::MODO_ENTRAR);
            $mform->setType('reglasdeuda_modo', PARAM_ALPHA);
        }
        $cm = $formwrapper->get_coursemodule();
        $actual = $cm ? self::de_actividad((int)$cm->id) : null;
        $mform->setDefault('reglasdeuda_regla', $actual ? (int)$actual->reglaid : 0);
        $mform->setDefault('reglasdeuda_modo', $actual ? $actual->modo : self::MODO_ENTREGAR);
    }

    /**
     * Guarda la configuracion al guardar la actividad.
     *
     * @param \stdClass $data datos del formulario de la actividad (con ->coursemodule)
     */
    public static function guardar(\stdClass $data): void {
        global $DB;
        if (!property_exists($data, 'reglasdeuda_regla') || empty($data->coursemodule)) {
            return; // El formulario no tenia el campo (no hay reglas con recursos).
        }
        $cmid = (int)$data->coursemodule;
        $reglaid = (int)$data->reglasdeuda_regla;
        $modo = ($data->reglasdeuda_modo ?? '') === self::MODO_ENTRAR ? self::MODO_ENTRAR : self::MODO_ENTREGAR;
        $actual = $DB->get_record(self::TABLA, ['cmid' => $cmid]);
        if (!$reglaid) {
            if ($actual) {
                $DB->delete_records(self::TABLA, ['id' => $actual->id]);
            }
        } else if ($actual) {
            $actual->reglaid = $reglaid;
            $actual->modo = $modo;
            $DB->update_record(self::TABLA, $actual);
        } else {
            $DB->insert_record(self::TABLA, (object)['cmid' => $cmid, 'reglaid' => $reglaid, 'modo' => $modo]);
        }
        unset(self::$memo[$cmid]);
    }

    /**
     * La pagina actual es la accion bloqueada en modo "entregar".
     *
     * @param string $modname
     * @param string $script
     * @return bool
     */
    public static function es_accion_de_entrega(string $modname, string $script): bool {
        if ($modname === 'quiz') {
            return in_array($script, ['/mod/quiz/startattempt.php', '/mod/quiz/attempt.php',
                '/mod/quiz/processattempt.php', '/mod/quiz/summary.php'], true);
        }
        if ($modname === 'assign') {
            $accion = optional_param('action', '', PARAM_ALPHA);
            return $script === '/mod/assign/view.php'
                && in_array($accion, ['editsubmission', 'savesubmission', 'submit', 'confirmsubmit', 'removesubmission'], true);
        }
        return false;
    }
}
