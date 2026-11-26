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
 * Ajustes y paginas de administracion.
 *
 * @package    local_reglasdeuda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$ADMIN->add('localplugins', new admin_category('local_reglasdeuda_categoria',
    new lang_string('pluginname', 'local_reglasdeuda')));

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_reglasdeuda', new lang_string('ajustesgenerales', 'local_reglasdeuda'));
    $settings->add(new admin_setting_configcheckbox('local_reglasdeuda/habilitado',
        new lang_string('habilitado', 'local_reglasdeuda'),
        new lang_string('habilitado_desc', 'local_reglasdeuda'), 1));
    $settings->add(new admin_setting_configcheckbox('local_reglasdeuda/hostsinternos',
        new lang_string('hostsinternos', 'local_reglasdeuda'),
        new lang_string('hostsinternos_desc', 'local_reglasdeuda'), 0));
    $ADMIN->add('local_reglasdeuda_categoria', $settings);
}

$ADMIN->add('local_reglasdeuda_categoria', new admin_externalpage('local_reglasdeuda_reglas',
    new lang_string('reglas', 'local_reglasdeuda'),
    new moodle_url('/local/reglasdeuda/index.php'), 'local/reglasdeuda:gestionar'));

$ADMIN->add('local_reglasdeuda_categoria', new admin_externalpage('local_reglasdeuda_contrato',
    new lang_string('contrato', 'local_reglasdeuda'),
    new moodle_url('/local/reglasdeuda/contrato.php'), 'local/reglasdeuda:gestionar'));
