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
 * Decide si una regla se aplica a un usuario consultando su API (con cache).
 *
 * @package    local_reglasdeuda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class evaluador {

    /** Segundos que se recuerda un error de la API (evita martillar una API caida). */
    const TTL_ERROR = 30;

    /** @var array memo por peticion: evaluaciones y exenciones (la pagina del curso pregunta muchas veces). */
    private static $memo = ['ev' => [], 'exento' => []];

    /**
     * El usuario nunca queda afectado por la regla: admin, capacidad de exento o rol exento.
     *
     * @param \stdClass $regla
     * @param int $userid
     * @return bool
     */
    public static function esta_exento(\stdClass $regla, int $userid): bool {
        global $DB;
        $clave = $regla->id . '_' . $userid;
        if (isset(self::$memo['exento'][$clave])) {
            return self::$memo['exento'][$clave];
        }
        $exento = is_siteadmin($userid)
            || has_capability('local/reglasdeuda:exento', \context_system::instance(), $userid);
        $roles = array_map('intval', reglas::csv($regla->rolesexentos));
        if (!$exento && $roles) {
            [$insql, $params] = $DB->get_in_or_equal($roles, SQL_PARAMS_NAMED);
            $params['userid'] = $userid;
            $exento = $DB->record_exists_select('role_assignments', "userid = :userid AND roleid $insql", $params);
        }
        return self::$memo['exento'][$clave] = $exento;
    }

    /**
     * Clave de cache: incluye timemodified para que editar una regla invalide solo sus resultados.
     *
     * @param \stdClass $regla
     * @param int $userid
     * @return string
     */
    private static function clave(\stdClass $regla, int $userid): string {
        return 'r' . (int)$regla->id . 't' . (int)$regla->timemodified . 'u' . $userid;
    }

    /**
     * Evalua la regla para el usuario.
     *
     * La cache es por (regla, usuario) y no depende del curso: la deuda es de la persona. Si la
     * plantilla usa variables de curso, el resultado cacheado corresponde a la primera consulta.
     *
     * @param \stdClass $regla
     * @param \stdClass $usuario registro completo de usuario
     * @param array $contexto ['curso' => stdClass|null, 'cmid' => int|null]
     * @param bool $usarcache false en la pantalla Probar (ni lee ni escribe la cache)
     * @param bool $permitirhttp false: solo cache; sin dato cacheado no se restringe (cron, otros usuarios)
     * @return array [
     *     'aplica' => bool   la accion debe ejecutarse,
     *     'error' => string  vacio si la consulta fue correcta,
     *     'resultado' => bool|null valor interpretado de la API,
     *     'datos' => array   variables planas 'datos.x',
     *     'mensajeapi' => string mensaje enviado por la API (texto plano),
     *     'peticion', 'http', 'respuesta', 'duracionms', 'cache' (diagnostico)
     * ]
     */
    public static function evaluar(\stdClass $regla, \stdClass $usuario, array $contexto = [], bool $usarcache = true,
            bool $permitirhttp = true): array {
        $clave = self::clave($regla, (int)$usuario->id);
        if ($usarcache) {
            if (isset(self::$memo['ev'][$clave])) {
                return self::$memo['ev'][$clave];
            }
            $guardado = \cache::make('local_reglasdeuda', 'resultados')->get($clave);
            if ($guardado !== false && $guardado['hasta'] > time()) {
                $guardado['cache'] = true;
                return self::$memo['ev'][$clave] = $guardado;
            }
            if (!$permitirhttp) {
                return self::vacio();
            }
        }

        $resultado = self::consultar($regla, $usuario, $contexto);
        $ttl = $resultado['error'] === '' ? max(reglas::TTL_MINIMO, (int)$regla->cachettl) : self::TTL_ERROR;
        $resultado['hasta'] = time() + $ttl;
        $resultado['cache'] = false;
        if ($usarcache) {
            $guardar = $resultado;
            unset($guardar['peticion'], $guardar['respuesta']); // Solo diagnostico; no ocupar cache.
            \cache::make('local_reglasdeuda', 'resultados')->set($clave, $guardar);
            self::$memo['ev'][$clave] = $resultado;
        }
        return $resultado;
    }

    /**
     * Borra los resultados cacheados de un usuario ("Ya pague, volver a verificar" y usuario borrado).
     *
     * @param int $userid
     */
    public static function olvidar_usuario(int $userid): void {
        $claves = [];
        foreach (reglas::todas() as $regla) {
            $claves[] = self::clave($regla, $userid);
            unset(self::$memo['ev'][self::clave($regla, $userid)]);
        }
        if ($claves) {
            \cache::make('local_reglasdeuda', 'resultados')->delete_many($claves);
        }
    }

    /**
     * Limpia el memo por peticion (pruebas).
     */
    public static function reiniciar_memo(): void {
        self::$memo = ['ev' => [], 'exento' => []];
    }

    /**
     * @return array evaluacion "no aplica" sin consulta
     */
    private static function vacio(): array {
        return [
            'aplica' => false,
            'error' => '',
            'resultado' => null,
            'datos' => [],
            'mensajeapi' => '',
            'peticion' => '',
            'http' => 0,
            'respuesta' => '',
            'duracionms' => 0,
            'cache' => false,
        ];
    }

    /**
     * Consulta la API sin cache.
     *
     * @param \stdClass $regla
     * @param \stdClass $usuario
     * @param array $contexto
     * @return array ver evaluar()
     */
    private static function consultar(\stdClass $regla, \stdClass $usuario, array $contexto): array {
        $salida = self::vacio();

        $valores = parametros::valores($regla, $usuario, $faltante);
        $peticion = cliente_api::peticion($regla, $valores);
        $salida['peticion'] = cliente_api::describir($regla, $peticion);
        if ($faltante !== null) {
            // Sin el dato no hay a quien consultar (p.ej. usuario sin numero de ID).
            $salida['error'] = get_string('error_sinvalor', 'local_reglasdeuda', $faltante);
            return self::aplicar_politica_error($regla, $salida);
        }

        $http = cliente_api::enviar($regla, $peticion);
        $salida['http'] = $http['http'];
        $salida['respuesta'] = $http['cuerpo'];
        $salida['duracionms'] = $http['duracionms'];

        if ($http['error'] !== '') {
            $salida['error'] = $http['error'];
            return self::aplicar_politica_error($regla, $salida);
        }
        if ($http['http'] !== 200) {
            $salida['error'] = get_string('error_http', 'local_reglasdeuda', $http['http']);
            return self::aplicar_politica_error($regla, $salida);
        }
        $json = json_decode($http['cuerpo'], true);
        if (!is_array($json)) {
            $salida['error'] = get_string('error_json', 'local_reglasdeuda');
            return self::aplicar_politica_error($regla, $salida);
        }
        $valor = plantilla::a_booleano(plantilla::obtener_ruta($json, $regla->rutaresultado));
        if ($valor === null) {
            $salida['error'] = get_string('error_resultado', 'local_reglasdeuda', $regla->rutaresultado);
            return self::aplicar_politica_error($regla, $salida);
        }

        $datos = plantilla::obtener_ruta($json, $regla->rutadatos);
        $mensaje = plantilla::obtener_ruta($json, $regla->rutamensaje);
        $salida['resultado'] = $valor;
        $salida['aplica'] = ($valor === (bool)$regla->valoractiva);
        $salida['datos'] = plantilla::aplanar($datos ?? [], 'datos');
        $salida['mensajeapi'] = is_scalar($mensaje) ? trim((string)$mensaje) : '';
        return $salida;
    }

    /**
     * Ante un error, la regla decide: dejar pasar (por defecto) o aplicar la accion.
     *
     * @param \stdClass $regla
     * @param array $salida
     * @return array
     */
    public static function aplicar_politica_error(\stdClass $regla, array $salida): array {
        $salida['aplica'] = ($regla->errorpolitica === 'aplicar');
        // error_log y no debugging(): esto corre dentro de require_login, a veces justo antes de un redirect.
        error_log('local_reglasdeuda: regla ' . $regla->id . ': ' . $salida['error']);
        return $salida;
    }
}
