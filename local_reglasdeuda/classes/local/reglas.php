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
 * Acceso a la tabla de reglas.
 *
 * @package    local_reglasdeuda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class reglas {

    const TABLA = 'local_reglasdeuda_reglas';

    const ACCION_BLOQUEAR = 'bloquear';
    const ACCION_RESTRINGIR = 'restringir';
    const ACCION_AVISAR = 'avisar';

    /** Tiempo minimo de cache (s): evita una consulta HTTP por cada pagina. */
    const TTL_MINIMO = 60;

    /** Tiempo maximo de cache (s): coincide con el ttl de la definicion de cache 'resultados'. */
    const TTL_MAXIMO = DAYSECS;

    /**
     * Valores por defecto de una regla nueva.
     *
     * @return \stdClass
     */
    public static function por_defecto(): \stdClass {
        return (object)[
            'id' => 0,
            'nombre' => '',
            'activa' => 0,
            'orden' => 0,
            'url' => '',
            'autenticacion' => 'bearer',
            'cabeceranombre' => 'X-API-Key',
            'token' => '',
            'timeout' => 5,
            'metodo' => cliente_api::POST_JSON,
            'parametros' => json_encode([['nombre' => 'identificacion', 'campo' => 'idnumber', 'valor' => '']]),
            'rutaresultado' => 'resultado',
            'rutadatos' => 'datos',
            'rutamensaje' => 'mensaje',
            'valoractiva' => 1,
            'errorpolitica' => 'permitir',
            'cachettl' => 900,
            'accion' => self::ACCION_AVISAR,
            'actividades' => '',
            'globalactivo' => 0,
            'globalmodulos' => '',
            'globalcategorias' => '',
            'globalcursos' => '',
            'rolesaplica' => self::roles_estudiante(),
            'titulo' => '',
            'mensaje' => '',
            'mensajeformat' => FORMAT_HTML,
            'botontexto' => '',
            'botonurl' => '{datos.url_pago}',
            'botonurlrespaldo' => '',
            'nuevapestana' => 1,
            'estilo' => 'warning',
            'respuestaejemplo' => '',
        ];
    }

    /**
     * @param int $id
     * @return \stdClass|null
     */
    public static function obtener(int $id): ?\stdClass {
        global $DB;
        return $DB->get_record(self::TABLA, ['id' => $id]) ?: null;
    }

    /**
     * Todas las reglas ordenadas.
     *
     * @return \stdClass[]
     */
    public static function todas(): array {
        global $DB;
        return $DB->get_records(self::TABLA, null, 'orden ASC, id ASC');
    }

    /**
     * Reglas activas (cacheadas; se invalidan al guardar o borrar).
     *
     * @return \stdClass[]
     */
    public static function activas(): array {
        global $DB;
        $cache = \cache::make('local_reglasdeuda', 'reglas');
        $activas = $cache->get('activas');
        if ($activas === false) {
            $activas = $DB->get_records(self::TABLA, ['activa' => 1], 'orden ASC, id ASC');
            $cache->set('activas', $activas);
        }
        return $activas;
    }

    /**
     * Reglas activas con una accion concreta.
     *
     * @param string $accion
     * @return \stdClass[]
     */
    public static function activas_por_accion(string $accion): array {
        return array_filter(self::activas(), function($r) use ($accion) {
            return $r->accion === $accion;
        });
    }

    /**
     * Crea o actualiza una regla.
     *
     * @param \stdClass $regla
     * @return int id
     */
    public static function guardar(\stdClass $regla): int {
        global $DB;
        $regla->timemodified = time();
        if (!empty($regla->id)) {
            $DB->update_record(self::TABLA, $regla);
            $id = (int)$regla->id;
        } else {
            unset($regla->id);
            $regla->timecreated = $regla->timemodified;
            $id = (int)$DB->insert_record(self::TABLA, $regla);
        }
        self::invalidar_caches();
        return $id;
    }

    /**
     * @param int $id
     */
    public static function borrar(int $id): void {
        global $DB;
        $DB->delete_records(self::TABLA, ['id' => $id]);
        self::invalidar_caches();
    }

    /**
     * Invalida la lista de reglas activas. Los resultados no se purgan: su clave incluye timemodified,
     * asi editar una regla solo invalida los suyos y no fuerza a todos los usuarios a consultar a la vez.
     */
    public static function invalidar_caches(): void {
        \cache::make('local_reglasdeuda', 'reglas')->purge();
    }

    /**
     * Ids (CSV) de los roles de arquetipo estudiante: a quienes aplica por defecto una regla nueva.
     *
     * @return string
     */
    public static function roles_estudiante(): string {
        return implode(',', array_map(function($rol) {
            return (int)$rol->id;
        }, get_archetype_roles('student')));
    }

    /**
     * CSV a lista de cadenas no vacias.
     *
     * @param string|null $csv
     * @return string[]
     */
    public static function csv(?string $csv): array {
        return array_values(array_filter(array_map('trim', explode(',', (string)$csv)), 'strlen'));
    }

    /**
     * Acciones disponibles para un selector.
     *
     * @return array
     */
    public static function opciones_accion(): array {
        return [
            self::ACCION_BLOQUEAR => get_string('accion_bloquear', 'local_reglasdeuda'),
            self::ACCION_RESTRINGIR => get_string('accion_restringir', 'local_reglasdeuda'),
            self::ACCION_AVISAR => get_string('accion_avisar', 'local_reglasdeuda'),
        ];
    }
}
