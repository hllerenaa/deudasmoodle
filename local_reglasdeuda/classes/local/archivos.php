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
 * Verificacion de los archivos instalados contra archivos.json (lo genera build.py con el sha1 de cada
 * archivo del ZIP). Sirve para detectar archivos que el antivirus del hosting borro o modifico.
 *
 * @package    local_reglasdeuda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class archivos {

    /**
     * Compara los archivos del plugin con el manifiesto.
     *
     * @return array ['faltan' => string[], 'cambiados' => string[], 'sinmanifiesto' => bool]
     */
    public static function verificar(): array {
        $raiz = dirname(__DIR__, 2);
        $manifiesto = json_decode((string)@file_get_contents($raiz . '/archivos.json'), true);
        if (!is_array($manifiesto)) {
            return ['faltan' => [], 'cambiados' => [], 'sinmanifiesto' => true];
        }
        $faltan = [];
        $cambiados = [];
        foreach ($manifiesto as $ruta => $sha1) {
            $archivo = $raiz . '/' . $ruta;
            if (!is_file($archivo)) {
                $faltan[] = $ruta;
            } else if (sha1_file($archivo) !== $sha1) {
                $cambiados[] = $ruta;
            }
        }
        return ['faltan' => $faltan, 'cambiados' => $cambiados, 'sinmanifiesto' => false];
    }

    /**
     * require_once de un archivo del plugin; si falta, un error claro en lugar de "Failed opening required".
     *
     * @param string $ruta relativa a la carpeta del plugin
     * @throws \moodle_exception
     */
    public static function requerir(string $ruta): void {
        $archivo = dirname(__DIR__, 2) . '/' . $ruta;
        if (!is_file($archivo)) {
            throw new \moodle_exception('archivofaltante', 'local_reglasdeuda', '', $ruta);
        }
        require_once($archivo);
    }

    /**
     * Aviso HTML si faltan o cambiaron archivos ('' si todo esta bien).
     *
     * @param bool $detalle incluir la lista de archivos
     * @return string
     */
    public static function aviso(bool $detalle = false): string {
        global $OUTPUT;
        $r = self::verificar();
        if (!$r['faltan'] && !$r['cambiados']) {
            return '';
        }
        $texto = get_string('verificacion_problema', 'local_reglasdeuda',
            ['faltan' => count($r['faltan']), 'cambiados' => count($r['cambiados'])]);
        if ($detalle) {
            $lista = array_merge(
                array_map(function($f) {
                    return get_string('verificacion_falta', 'local_reglasdeuda', s($f));
                }, $r['faltan']),
                array_map(function($f) {
                    return get_string('verificacion_cambiado', 'local_reglasdeuda', s($f));
                }, $r['cambiados'])
            );
            $texto .= \html_writer::alist($lista);
        }
        return $OUTPUT->notification($texto, 'error');
    }
}
