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
 * Actualizaciones de base de datos.
 *
 * @package    local_reglasdeuda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_reglasdeuda_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2026112601) {
        $tabla = new xmldb_table('local_reglasdeuda_reglas');
        // Instalaciones de la 0.1.0 anteriores a "un solo plugin": faltan estos campos.
        foreach (['actividades', 'globalcursos', 'parametros'] as $nombre) {
            $campo = new xmldb_field($nombre, XMLDB_TYPE_TEXT, null, null, null, null, null);
            if (!$dbman->field_exists($tabla, $campo)) {
                $dbman->add_field($tabla, $campo);
            }
        }
        $metodo = new xmldb_field('metodo', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'post_json');
        if (!$dbman->field_exists($tabla, $metodo)) {
            $dbman->add_field($tabla, $metodo);
        }

        // La plantilla JSON se reemplaza por la tabla de parametros: se conserva el identificador elegido.
        $identificador = new xmldb_field('identificador');
        if ($dbman->field_exists($tabla, $identificador)) {
            foreach ($DB->get_records('local_reglasdeuda_reglas', null, '', 'id, identificador, parametros') as $r) {
                if (empty($r->parametros)) {
                    $DB->set_field('local_reglasdeuda_reglas', 'parametros', json_encode([
                        ['nombre' => 'identificador', 'campo' => $r->identificador ?: 'idnumber', 'valor' => ''],
                    ]), ['id' => $r->id]);
                }
            }
            $dbman->drop_field($tabla, $identificador);
        }
        $plantilla = new xmldb_field('plantilla');
        if ($dbman->field_exists($tabla, $plantilla)) {
            $dbman->drop_field($tabla, $plantilla);
        }
        set_config('irconfiguracion', 1, 'local_reglasdeuda');
        // Los ajustes pasaron de settings.php a la pestana Ajustes; asegurar valores.
        if (get_config('local_reglasdeuda', 'habilitado') === false) {
            set_config('habilitado', 1, 'local_reglasdeuda');
        }
        if (get_config('local_reglasdeuda', 'hostsinternos') === false) {
            set_config('hostsinternos', 0, 'local_reglasdeuda');
        }
        upgrade_plugin_savepoint(true, 2026112601, 'local', 'reglasdeuda');
    }

    if ($oldversion < 2026112603) {
        // "Roles exentos" pasa a "Roles a los que aplica": las reglas existentes quedan con Estudiante.
        $tabla = new xmldb_table('local_reglasdeuda_reglas');
        $nuevo = new xmldb_field('rolesaplica', XMLDB_TYPE_TEXT, null, null, null, null, null);
        if (!$dbman->field_exists($tabla, $nuevo)) {
            $dbman->add_field($tabla, $nuevo);
            $estudiante = implode(',', array_map(function($rol) {
                return (int)$rol->id;
            }, get_archetype_roles('student')));
            $DB->set_field('local_reglasdeuda_reglas', 'rolesaplica', $estudiante);
        }
        $viejo = new xmldb_field('rolesexentos');
        if ($dbman->field_exists($tabla, $viejo)) {
            $dbman->drop_field($tabla, $viejo);
        }
        \cache::make('local_reglasdeuda', 'reglas')->purge();
        upgrade_plugin_savepoint(true, 2026112603, 'local', 'reglasdeuda');
    }
    if ($oldversion < 2026112612) {
        $tabla = new xmldb_table('local_reglasdeuda_reglas');
        $campo = new xmldb_field('recursos', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($tabla, $campo)) {
            $dbman->add_field($tabla, $campo);
        }
        // "Bloquear recursos" deja de ser una accion: pasa a la opcion "Permitir bloquear recursos".
        $DB->execute("UPDATE {local_reglasdeuda_reglas} SET recursos = 1, accion = 'ninguna' WHERE accion = 'restringir'");

        $cm = new xmldb_table('local_reglasdeuda_cm');
        if (!$dbman->table_exists($cm)) {
            $cm->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $cm->add_field('cmid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $cm->add_field('reglaid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            $cm->add_field('modo', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'entregar');
            $cm->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $cm->add_index('cmid', XMLDB_INDEX_UNIQUE, ['cmid']);
            $dbman->create_table($cm);
        }
        \cache::make('local_reglasdeuda', 'reglas')->purge();
        upgrade_plugin_savepoint(true, 2026112612, 'local', 'reglasdeuda');
    }
    return true;
}
