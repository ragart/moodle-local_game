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

    $events = \local_game\event_manager::get_event_list();
    $events = array_reduce($events, function($result, $iteration) {
        if (!array_key_exists($iteration->component, $result)) {
            $result[$iteration->component] = new stdClass;
            $result[$iteration->component]->fullname = $iteration->componentfullname;
            $result[$iteration->component]->events = [];
        }
        $result[$iteration->component]->events[] = $iteration;
        return $result;
    }, []);
    uasort($events, function($a, $b) {
        return strcmp(strtolower($a->fullname), strtolower($b->fullname));
    });

    foreach ($events as $componentname => $component) {

        $settings->add(new admin_setting_heading(
            'local_game/' . $componentname,
            new lang_string('settings:eventcomponentdefaults', 'local_game', ['componentfullname' => $component->fullname]),
            new lang_string('settings:eventcomponentdefaults_desc', 'local_game', ['component' => $componentname, 'componentfullname' => $component->fullname])
        ));

        foreach ($component->events as $event) {
            $settings->add(new admin_setting_configcheckbox(
                'local_game/' . ltrim(str_replace('\\', '_', $event->eventname), '_') . '_enabled',
                new lang_string('enable'),
                null,
                0
            ));
        }

    }

}
