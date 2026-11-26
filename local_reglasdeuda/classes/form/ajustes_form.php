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

namespace local_reglasdeuda\form;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Ajustes generales del plugin (pestana Ajustes).
 *
 * @package    local_reglasdeuda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ajustes_form extends \moodleform {

    protected function definition() {
        $f = $this->_form;
        $c = 'local_reglasdeuda';
        $f->addElement('advcheckbox', 'habilitado', get_string('habilitado', $c));
        $f->addElement('static', 'habilitado_desc', '', get_string('habilitado_desc', $c));
        $f->addElement('advcheckbox', 'hostsinternos', get_string('hostsinternos', $c));
        $f->addElement('static', 'hostsinternos_desc', '', get_string('hostsinternos_desc', $c));
        $this->add_action_buttons(false);
    }
}
