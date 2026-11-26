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

namespace availability_reglasdeuda;

use core_availability\info;
use local_reglasdeuda\local\evaluador;
use local_reglasdeuda\local\reglas;

/**
 * Disponible si la regla de deuda NO se activa para el usuario.
 *
 * JSON guardado: {"type": "reglasdeuda", "id": <id de la regla>}.
 *
 * Se ejecuta mientras Moodle construye modinfo: no formatea el mensaje completo aqui (los filtros
 * podrian volver a pedir modinfo) y solo consulta la API para el usuario actual en una peticion web.
 * Para otros usuarios (cron, informes) usa solo la cache.
 *
 * @package    availability_reglasdeuda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class condition extends \core_availability\condition {

    /** @var int id de la regla en local_reglasdeuda_reglas */
    protected $reglaid;

    /**
     * @param \stdClass $structure
     */
    public function __construct($structure) {
        if (!isset($structure->id) || !is_int($structure->id)) {
            throw new \coding_exception('Falta o es invalido ->id en la condicion reglasdeuda');
        }
        $this->reglaid = $structure->id;
    }

    public function save() {
        return self::get_json($this->reglaid);
    }

    /**
     * Estructura JSON para crear la condicion por codigo.
     *
     * @param int $reglaid
     * @return \stdClass
     */
    public static function get_json(int $reglaid): \stdClass {
        return (object)['type' => 'reglasdeuda', 'id' => $reglaid];
    }

    public function is_available($not, info $info, $grabthelot, $userid) {
        $disponible = !$this->esta_restringido((int)$userid, $info);
        return $not ? !$disponible : $disponible;
    }

    public function get_description($full, $not, info $info) {
        global $USER, $DB;
        $activas = reglas::activas();
        $nombre = isset($activas[$this->reglaid]) ? $activas[$this->reglaid]->nombre
            : $DB->get_field(reglas::TABLA, 'nombre', ['id' => $this->reglaid]);
        $nombre = $nombre !== false ? self::description_format_string($nombre)
            : get_string('reglaborrada', 'availability_reglasdeuda');
        $texto = get_string($not ? 'requiere_aplica' : 'requiere_noaplica', 'availability_reglasdeuda', $nombre);

        // Al usuario restringido: enlace al detalle (total, boton de pago), que se formatea en su propia pagina.
        if (!$not && isloggedin() && !isguestuser() && $this->esta_restringido((int)$USER->id, $info)) {
            $parametros = ['regla' => $this->reglaid];
            if ($info instanceof \core_availability\info_module) {
                $parametros['cmid'] = $info->get_course_module()->id;
            } else {
                $parametros['curso'] = $info->get_course()->id;
            }
            $texto .= ' ' . \html_writer::link(new \moodle_url('/local/reglasdeuda/bloqueado.php', $parametros),
                get_string('verdetalle', 'availability_reglasdeuda'));
        }
        return $texto;
    }

    protected function get_debug_string() {
        return '#' . $this->reglaid;
    }

    /**
     * La regla esta activa, el usuario no esta exento y la API dice que se activa.
     *
     * @param int $userid
     * @param info $info
     * @return bool
     */
    private function esta_restringido(int $userid, info $info): bool {
        global $USER;
        $regla = reglas::activas()[$this->reglaid] ?? null;
        if (!$regla || $regla->accion !== reglas::ACCION_RESTRINGIR || !get_config('local_reglasdeuda', 'habilitado')) {
            return false;
        }
        try {
            if (evaluador::esta_exento($regla, $userid)) {
                return false;
            }
            $esactual = ($userid == $USER->id);
            $usuario = $esactual ? $USER : \core_user::get_user($userid);
            if (!$usuario || isguestuser($usuario)) {
                return false;
            }
            // HTTP solo para el usuario actual en una peticion web; cron e informes usan la cache.
            $permitirhttp = $esactual && !CLI_SCRIPT;
            return evaluador::evaluar($regla, $usuario, $this->contexto($info), true, $permitirhttp)['aplica'];
        } catch (\Throwable $e) {
            // Un fallo nunca debe romper la pagina del curso: se aplica la politica de error de la regla.
            return evaluador::aplicar_politica_error($regla, ['error' => $e->getMessage()])['aplica'];
        }
    }

    /**
     * @param info $info
     * @return array
     */
    private function contexto(info $info): array {
        $cmid = null;
        if ($info instanceof \core_availability\info_module) {
            $cmid = (int)$info->get_course_module()->id;
        }
        return ['curso' => $info->get_course(), 'cmid' => $cmid];
    }
}
