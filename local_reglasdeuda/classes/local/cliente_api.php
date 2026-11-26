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
 * Llamada HTTP a la API de una regla: GET con parametros en la URL, POST JSON o POST formulario.
 *
 * @package    local_reglasdeuda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cliente_api {

    const GET = 'get';
    const POST_JSON = 'post_json';
    const POST_FORM = 'post_form';

    /**
     * Como quedaria la peticion (para Probar y el Contrato).
     *
     * @param \stdClass $regla
     * @param array $valores nombre => valor
     * @return array ['metodo' => 'GET'|'POST', 'url' => string, 'cuerpo' => string, 'tipo' => content-type|'']
     */
    public static function peticion(\stdClass $regla, array $valores): array {
        switch ($regla->metodo) {
            case self::GET:
                $separador = strpos($regla->url, '?') === false ? '?' : '&';
                $url = $valores ? $regla->url . $separador . http_build_query($valores, '', '&', PHP_QUERY_RFC3986) : $regla->url;
                return ['metodo' => 'GET', 'url' => $url, 'cuerpo' => '', 'tipo' => ''];
            case self::POST_FORM:
                return ['metodo' => 'POST', 'url' => $regla->url, 'tipo' => 'application/x-www-form-urlencoded',
                    'cuerpo' => http_build_query($valores, '', '&', PHP_QUERY_RFC3986)];
            default:
                return ['metodo' => 'POST', 'url' => $regla->url, 'tipo' => 'application/json',
                    'cuerpo' => json_encode((object)parametros::con_tipos($regla, $valores), JSON_UNESCAPED_UNICODE |
                        JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE | JSON_PRESERVE_ZERO_FRACTION)];
        }
    }

    /**
     * Ejecuta la peticion.
     *
     * @param \stdClass $regla
     * @param array $peticion de peticion()
     * @return array ['http' => int, 'cuerpo' => string, 'error' => string, 'duracionms' => int]
     */
    public static function enviar(\stdClass $regla, array $peticion): array {
        $cabeceras = ['Accept: application/json'];
        if ($peticion['tipo'] !== '') {
            $cabeceras[] = 'Content-Type: ' . $peticion['tipo'];
        }
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
        if ($peticion['metodo'] === 'GET') {
            $respuesta = $curl->get($peticion['url'], [], $opciones);
        } else {
            $respuesta = $curl->post($peticion['url'], $peticion['cuerpo'], $opciones);
        }
        $duracion = (int)round((microtime(true) - $inicio) * 1000);

        // Error de red/tiempo (errno) o URL bloqueada por el security helper (solo deja $curl->error).
        $error = '';
        if (!empty($curl->error)) {
            $error = (string)$curl->error;
        } else if ($curl->get_errno()) {
            $error = 'cURL ' . $curl->get_errno();
        }

        return [
            'http' => (int)($curl->get_info()['http_code'] ?? 0),
            'cuerpo' => is_string($respuesta) ? $respuesta : '',
            'error' => $error,
            'duracionms' => $duracion,
        ];
    }

    /**
     * Texto legible de la peticion (pantalla Probar y Contrato).
     *
     * @param \stdClass $regla
     * @param array $peticion
     * @return string
     */
    public static function describir(\stdClass $regla, array $peticion): string {
        $lineas = [$peticion['metodo'] . ' ' . $peticion['url']];
        if ($peticion['tipo'] !== '') {
            $lineas[] = 'Content-Type: ' . $peticion['tipo'];
        }
        if ($regla->autenticacion === 'bearer') {
            $lineas[] = 'Authorization: Bearer ****';
        } else if ($regla->autenticacion === 'cabecera') {
            $lineas[] = $regla->cabeceranombre . ': ****';
        }
        if ($peticion['cuerpo'] !== '') {
            $cuerpo = $peticion['cuerpo'];
            $json = json_decode($cuerpo);
            if ($json !== null) {
                $cuerpo = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
            $lineas[] = '';
            $lineas[] = $cuerpo;
        }
        return implode("\n", $lineas);
    }

    /**
     * Metodos para el selector.
     *
     * @return array
     */
    public static function metodos(): array {
        return [
            self::GET => get_string('metodo_get', 'local_reglasdeuda'),
            self::POST_JSON => get_string('metodo_post_json', 'local_reglasdeuda'),
            self::POST_FORM => get_string('metodo_post_form', 'local_reglasdeuda'),
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
