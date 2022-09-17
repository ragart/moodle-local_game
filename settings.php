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
 * Settings.
 *
 * @package    local_game
 * @copyright  2022 Salvador Banderas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {

    $settings = new admin_settingpage(
        'local_game',
        new lang_string('pluginname', 'local_game'));
        
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_heading(
        'local_game/general',
        new lang_string('settings:general', 'local_game'),
        null
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_game/enable',
        new lang_string('settings:enable', 'local_game'),
        new lang_string('settings:enable_desc', 'local_game'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_game/enableglobalinstance',
        new lang_string('settings:enableglobalinstance', 'local_game'),
        new lang_string('settings:enableglobalinstance_desc', 'local_game'),
        0
    ));

}
