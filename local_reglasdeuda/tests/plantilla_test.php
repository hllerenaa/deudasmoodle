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

namespace local_reglasdeuda;

use local_reglasdeuda\local\mensaje;
use local_reglasdeuda\local\plantilla;

/**
 * Pruebas de plantillas, variables y del armado del mensaje.
 *
 * @package    local_reglasdeuda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_reglasdeuda\local\plantilla
 * @covers     \local_reglasdeuda\local\mensaje
 */
final class plantilla_test extends \advanced_testcase {

    public function test_reemplazar_variables_conocidas_y_desconocidas(): void {
        $texto = plantilla::reemplazar('Hola {firstname}, debes {datos.total} {nada}.',
            ['firstname' => 'Ana', 'datos.total' => '10.50']);
        $this->assertSame('Hola Ana, debes 10.50 .', $texto);
    }

    public function test_reemplazar_escapa_valores(): void {
        $texto = plantilla::reemplazar('<b>{datos.x}</b>', ['datos.x' => '<script>alert(1)</script>'], 's');
        $this->assertSame('<b>&lt;script&gt;alert(1)&lt;/script&gt;</b>', $texto);
    }

    public function test_aplanar_y_obtener_ruta(): void {
        $datos = ['total' => 235.5, 'cliente' => ['nombre' => 'Ana', 'activo' => true], 'mal-clave' => 1, 'nulo' => null];
        $this->assertSame([
            'datos.total' => '235.5',
            'datos.cliente.nombre' => 'Ana',
            'datos.cliente.activo' => 'true',
            'datos.nulo' => '',
        ], plantilla::aplanar($datos));
        $this->assertSame('Ana', plantilla::obtener_ruta(['a' => ['b' => 'Ana']], 'a.b'));
        $this->assertNull(plantilla::obtener_ruta(['a' => 1], 'a.b'));
    }

    /**
     * @dataProvider booleanos
     * @param mixed $valor
     * @param bool|null $esperado
     */
    public function test_a_booleano($valor, ?bool $esperado): void {
        $this->assertSame($esperado, plantilla::a_booleano($valor));
    }

    /**
     * @return array
     */
    public static function booleanos(): array {
        return [
            [true, true], [false, false], [1, true], [0, false], ['true', true], ['FALSE', false],
            ['si', true], ['Sí', true], ['no', false], ['quizas', null], [null, null], [[1], null],
        ];
    }

    public function test_tipar_valores_fijos(): void {
        $this->assertSame(true, \local_reglasdeuda\local\parametros::tipar('TRUE'));
        $this->assertSame(false, \local_reglasdeuda\local\parametros::tipar('false'));
        $this->assertSame(50, \local_reglasdeuda\local\parametros::tipar('50'));
        $this->assertSame(0.0, \local_reglasdeuda\local\parametros::tipar('0.00'));
        $this->assertSame('0912345678', \local_reglasdeuda\local\parametros::tipar('0912345678'));
        $this->assertSame('idnumber', \local_reglasdeuda\local\parametros::tipar('idnumber'));
    }

    public function test_variables_de_ejemplo(): void {
        $ejemplo = '{"resultado": true, "datos": {"total": "1", "url_pago": "https://x", "det": {"a": 1}}}';
        $this->assertSame(['datos.total', 'datos.url_pago', 'datos.det.a'], plantilla::variables_de_ejemplo($ejemplo, 'datos'));
        $this->assertSame([], plantilla::variables_de_ejemplo('no json', 'datos'));
    }

    public function test_url_boton_usa_respaldo_y_rechaza_javascript(): void {
        $this->resetAfterTest();
        $regla = (object)['botonurl' => '{datos.url_pago}', 'botonurlrespaldo' => 'https://pagos.example.edu/'];
        $this->assertSame('https://pagos.example.edu/?r=1',
            mensaje::url_boton($regla, ['datos.url_pago' => 'https://pagos.example.edu/?r=1']));
        $this->assertSame('https://pagos.example.edu/', mensaje::url_boton($regla, ['datos.url_pago' => 'javascript:alert(1)']));
        $this->assertSame('https://pagos.example.edu/', mensaje::url_boton($regla, []));
        $regla->botonurlrespaldo = '';
        $this->assertSame('', mensaje::url_boton($regla, []));
    }
}
