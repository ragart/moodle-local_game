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
 * Instance configuration form.
 *
 * @package    local_game
 * @copyright  2022 Salvador Banderas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_game;

defined('MOODLE_INTERNAL') || die();

use moodleform;

/**
 * Instance configuration form.
 *
 * @copyright  2022 Salvador Banderas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class instance_form extends moodleform {

    /**
     * Form definition.
     */
    public function definition() {

        $mform = $this->_form;

        // Enable gamification in the context
        $mform->addElement('checkbox', 'enabled', get_string('enabled', 'local_game'));
        $mform->setDefault('enabled', 1);
        $mform->addHelpButton('enabled', 'enabled', 'local_game');

        // Use own instance or inherit from parent context

        // Multiselect of events to gamify

        $mform->addElement('text', 'points', get_string('points', 'local_game'));
        $mform->setType('points', PARAM_INT);
        $mform->addRule('points', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'frequency', get_string('frequency', 'local_game'));
        $mform->setType('frequency', PARAM_INT);
        $mform->addRule('frequency', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'event', get_string('event', 'local_game'));
        $mform->setType('event', PARAM_TEXT);
        $mform->addRule('event', get_string('required'), 'required', null, 'client');

        $this->add_action_buttons();
    }

    /**
     * Validate the form data.
     *
     * @param array $data The form data.
     * @param array $files The form files.
     * @return array The errors.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ($data['points'] == 0) {
            $errors['points'] = get_string('pointserror', 'local_game');
        }

        if ($data['frequency'] < 0) {
            $errors['frequency'] = get_string('frequencyerror', 'local_game');
        }

        return $errors;
    }
}
