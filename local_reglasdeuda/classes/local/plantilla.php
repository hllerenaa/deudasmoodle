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
 * Variables {nombre} y {datos.campo} de los mensajes.
 *
 * Sin dependencias de Moodle salvo en variables_usuario(), para poder probarse aislada.
 *
 * @package    local_reglasdeuda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plantilla {

    /** Patron de una variable: {firstname}, {datos.total}, {datos.cliente.nombre}. */
    const PATRON = '/\{([a-zA-Z0-9_]+(?:\.[a-zA-Z0-9_]+)*)\}/';

    /** Variables de usuario y curso siempre disponibles (nombre => clave de cadena de ayuda). */
    const VARIABLES_FIJAS = [
        'usuario' => ['firstname', 'lastname', 'fullname', 'username', 'idnumber', 'email'],
        'curso' => ['course_fullname', 'course_shortname', 'cmid'],
        'regla' => ['regla'],
    ];

    /**
     * Variables del usuario y del contexto (curso/actividad) para una regla.
     *
     * @param \stdClass $regla registro de la regla
     * @param \stdClass $usuario registro de usuario (con firstname, lastname, username, idnumber, email)
     * @param array $contexto ['curso' => stdClass|null, 'cmid' => int|null]
     * @return array nombre => valor (cadenas)
     */
    public static function variables_usuario(\stdClass $regla, \stdClass $usuario, array $contexto = []): array {
        $curso = $contexto['curso'] ?? null;
        return [
            'regla' => (string)$regla->nombre,
            'userid' => (string)($usuario->id ?? ''),
            'username' => (string)($usuario->username ?? ''),
            'idnumber' => (string)($usuario->idnumber ?? ''),
            'email' => (string)($usuario->email ?? ''),
            'firstname' => (string)($usuario->firstname ?? ''),
            'lastname' => (string)($usuario->lastname ?? ''),
            'fullname' => trim(($usuario->firstname ?? '') . ' ' . ($usuario->lastname ?? '')),
            'course_fullname' => $curso ? (string)$curso->fullname : '',
            'course_shortname' => $curso ? (string)$curso->shortname : '',
            'cmid' => isset($contexto['cmid']) ? (string)$contexto['cmid'] : '',
        ];
    }

    /**
     * Reemplaza variables en un texto. Las variables desconocidas quedan vacias.
     *
     * @param string $texto texto con {variables}
     * @param array $variables nombre => valor (planas, p.ej. 'datos.total')
     * @param callable|null $escapar funcion aplicada a cada valor (p.ej. s() para HTML)
     * @return string
     */
    public static function reemplazar(string $texto, array $variables, ?callable $escapar = null): string {
        return preg_replace_callback(self::PATRON, function($m) use ($variables, $escapar) {
            $valor = array_key_exists($m[1], $variables) ? (string)$variables[$m[1]] : '';
            return $escapar ? $escapar($valor) : $valor;
        }, $texto);
    }

    /**
     * Aplana un arreglo anidado a claves con punto: ['a' => ['b' => 1]] => ['datos.a.b' => '1'].
     *
     * @param mixed $datos
     * @param string $prefijo
     * @return array
     */
    public static function aplanar($datos, string $prefijo = 'datos'): array {
        $plano = [];
        if (!is_array($datos)) {
            if (is_scalar($datos) || $datos === null) {
                $plano[$prefijo] = self::escalar_a_texto($datos);
            }
            return $plano;
        }
        foreach ($datos as $clave => $valor) {
            if (!preg_match('/^[a-zA-Z0-9_]+$/', (string)$clave)) {
                continue; // No se podria escribir como {variable}.
            }
            $plano += self::aplanar($valor, $prefijo . '.' . $clave);
        }
        return $plano;
    }

    /**
     * Valor en una ruta con puntos: obtener_ruta(['a' => ['b' => 2]], 'a.b') => 2.
     *
     * @param array $json
     * @param string $ruta
     * @return mixed|null null si la ruta no existe
     */
    public static function obtener_ruta(array $json, string $ruta) {
        $actual = $json;
        foreach (explode('.', trim($ruta)) as $parte) {
            if (!is_array($actual) || !array_key_exists($parte, $actual)) {
                return null;
            }
            $actual = $actual[$parte];
        }
        return $actual;
    }

    /**
     * Interpreta el resultado de la API como booleano.
     *
     * @param mixed $valor
     * @return bool|null null si no es interpretable
     */
    public static function a_booleano($valor): ?bool {
        if (is_bool($valor)) {
            return $valor;
        }
        if (is_int($valor) || is_float($valor)) {
            return $valor != 0;
        }
        if (is_string($valor)) {
            $v = \core_text::strtolower(trim($valor));
            if (in_array($v, ['true', '1', 'si', 'sí', 'yes', 's'], true)) {
                return true;
            }
            if (in_array($v, ['false', '0', 'no', 'n', ''], true)) {
                return false;
            }
        }
        return null;
    }

    /**
     * Nombres de variables de la API descubiertos en una respuesta de ejemplo.
     *
     * @param string|null $ejemplojson respuesta de ejemplo
     * @param string $rutadatos ruta del objeto de datos (p.ej. 'datos')
     * @return string[] p.ej. ['datos.total', 'datos.url_pago']
     */
    public static function variables_de_ejemplo(?string $ejemplojson, string $rutadatos): array {
        if (empty($ejemplojson)) {
            return [];
        }
        $json = json_decode($ejemplojson, true);
        if (!is_array($json)) {
            return [];
        }
        $datos = self::obtener_ruta($json, $rutadatos);
        return array_keys(self::aplanar($datos ?? [], 'datos'));
    }

    /**
     * @param mixed $valor
     * @return string
     */
    private static function escalar_a_texto($valor): string {
        if (is_bool($valor)) {
            return $valor ? 'true' : 'false';
        }
        return $valor === null ? '' : (string)$valor;
    }
}
