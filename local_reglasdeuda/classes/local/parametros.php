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
 * Parametros que una regla envia a su API: nombre del parametro => campo de Moodle (o valor fijo).
 * Con campo y valor, el valor se usa por defecto cuando al usuario le falta el dato.
 *
 * Se guardan en la regla como JSON: [{"nombre": "cedula", "campo": "idnumber", "valor": ""}, ...].
 *
 * @package    local_reglasdeuda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class parametros {

    /** Campo especial: se envia el texto de "valor" tal cual. */
    const FIJO = '__fijo';

    /** Prefijo de los campos de perfil personalizados: profile_<shortname>. */
    const PERFIL = 'profile_';

    /** Campos estandar del usuario que se pueden enviar. */
    const ESTANDAR = ['idnumber', 'username', 'email', 'firstname', 'lastname', 'id'];

    /**
     * Opciones del selector "Campo de Moodle".
     *
     * @return array campo => etiqueta
     */
    public static function campos_disponibles(): array {
        global $DB;
        $campos = [
            'idnumber' => get_string('idnumber'),
            'username' => get_string('username'),
            'email' => get_string('email'),
            'firstname' => get_string('firstname'),
            'lastname' => get_string('lastname'),
            'id' => get_string('campo_id', 'local_reglasdeuda'),
        ];
        foreach ($DB->get_records('user_info_field', null, 'sortorder ASC', 'id, shortname, name') as $f) {
            $campos[self::PERFIL . $f->shortname] = get_string('campo_perfil', 'local_reglasdeuda', format_string($f->name));
        }
        $campos[self::FIJO] = get_string('campo_fijo', 'local_reglasdeuda');
        return $campos;
    }

    /**
     * Lista de parametros de la regla.
     *
     * @param \stdClass $regla
     * @return array [['nombre' => ..., 'campo' => ..., 'valor' => ...], ...]
     */
    public static function de_regla(\stdClass $regla): array {
        $lista = json_decode((string)($regla->parametros ?? ''), true);
        if (!is_array($lista)) {
            return [];
        }
        return array_values(array_filter(array_map(function($p) {
            return is_array($p) ? [
                'nombre' => trim((string)($p['nombre'] ?? '')),
                'campo' => (string)($p['campo'] ?? ''),
                'valor' => (string)($p['valor'] ?? ''),
            ] : null;
        }, $lista), function($p) {
            return $p && $p['nombre'] !== '';
        }));
    }

    /**
     * Valores a enviar para un usuario.
     *
     * @param \stdClass $regla
     * @param \stdClass $usuario
     * @param string|null $faltante salida: etiqueta del primer campo sin valor (no se puede consultar)
     * @return array nombre => valor
     */
    public static function valores(\stdClass $regla, \stdClass $usuario, ?string &$faltante = null): array {
        $faltante = null;
        $perfil = null;
        $valores = [];
        foreach (self::de_regla($regla) as $p) {
            if ($p['campo'] === self::FIJO) {
                $valor = $p['valor'];
            } else if (strpos($p['campo'], self::PERFIL) === 0) {
                if ($perfil === null) {
                    $perfil = self::perfil((int)$usuario->id);
                }
                $valor = (string)($perfil[substr($p['campo'], strlen(self::PERFIL))] ?? '');
            } else if (in_array($p['campo'], self::ESTANDAR, true)) {
                $valor = (string)($usuario->{$p['campo']} ?? '');
            } else {
                $valor = '';
            }
            if ($valor === '' && $p['campo'] !== self::FIJO) {
                if ($p['valor'] !== '') {
                    $valor = $p['valor']; // Valor por defecto cuando al usuario le falta el dato.
                } else if ($faltante === null) {
                    $faltante = self::campos_disponibles()[$p['campo']] ?? $p['campo'];
                }
            }
            $valores[$p['nombre']] = $valor;
        }
        return $valores;
    }

    /**
     * Nombre de parametro valido (sin espacios ni caracteres que rompan la URL o el JSON).
     *
     * @param string $nombre
     * @return bool
     */
    public static function nombre_valido(string $nombre): bool {
        return (bool)preg_match('/^[A-Za-z0-9_.\-\[\]]{1,100}$/', $nombre);
    }

    /**
     * Campos de perfil personalizados del usuario.
     *
     * @param int $userid
     * @return array shortname => valor
     */
    private static function perfil(int $userid): array {
        global $DB;
        $sql = "SELECT f.shortname, d.data
                  FROM {user_info_field} f
                  JOIN {user_info_data} d ON d.fieldid = f.id
                 WHERE d.userid = :userid";
        return $DB->get_records_sql_menu($sql, ['userid' => $userid]);
    }
}
