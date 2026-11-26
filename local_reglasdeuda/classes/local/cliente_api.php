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

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/filelib.php');

/**
 * Llamada HTTP a la API de una regla.
 *
 * @package    local_reglasdeuda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cliente_api {

    /**
     * Envia el cuerpo JSON por POST.
     *
     * @param \stdClass $regla
     * @param string $cuerpo JSON
     * @return array ['http' => int, 'cuerpo' => string, 'error' => string, 'duracionms' => int]
     */
    public static function enviar(\stdClass $regla, string $cuerpo): array {
        $cabeceras = ['Content-Type: application/json', 'Accept: application/json'];
        $token = trim((string)$regla->token);
        if ($token !== '') {
            if ($regla->autenticacion === 'bearer') {
                $cabeceras[] = 'Authorization: Bearer ' . $token;
            } else if ($regla->autenticacion === 'cabecera' && self::nombre_cabecera_valido((string)$regla->cabeceranombre)) {
                $cabeceras[] = $regla->cabeceranombre . ': ' . $token;
            }
        }

        // Por defecto se respeta la lista de hosts bloqueados de Moodle (evita SSRF a la red interna).
        $curl = new \curl(['ignoresecurity' => (bool)get_config('local_reglasdeuda', 'hostsinternos')]);
        $curl->setHeader($cabeceras);
        $timeout = max(1, min(30, (int)$regla->timeout));
        $opciones = [
            'CURLOPT_TIMEOUT' => $timeout,
            'CURLOPT_CONNECTTIMEOUT' => $timeout,
            'CURLOPT_FOLLOWLOCATION' => false,
        ];

        $inicio = microtime(true);
        $respuesta = $curl->post($regla->url, $cuerpo, $opciones);
        $duracion = (int)round((microtime(true) - $inicio) * 1000);

        $info = $curl->get_info();
        // Error de red/tiempo (errno) o URL bloqueada por el security helper (solo deja $curl->error).
        $error = '';
        if (!empty($curl->error)) {
            $error = (string)$curl->error;
        } else if ($curl->get_errno()) {
            $error = 'cURL ' . $curl->get_errno();
        }

        return [
            'http' => (int)($info['http_code'] ?? 0),
            'cuerpo' => is_string($respuesta) ? $respuesta : '',
            'error' => $error,
            'duracionms' => $duracion,
        ];
    }

    /**
     * Nombre de cabecera HTTP seguro (evita inyectar saltos de linea).
     *
     * @param string $nombre
     * @return bool
     */
    public static function nombre_cabecera_valido(string $nombre): bool {
        return (bool)preg_match('/^[A-Za-z0-9-]{1,100}$/', $nombre);
    }
}
