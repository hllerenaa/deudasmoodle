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
    return true;
}
