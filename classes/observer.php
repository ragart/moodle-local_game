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
 * Event observer callbacks.
 *
 * @package    local_game
 * @copyright  2022 Salvador Banderas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class local_game_observer {

    public static function course_module_completion_updated(core\event\course_module_completion_updated $event) {

    }

    public static function course_completion_updated(core\event\course_completion_updated $event) {

    }

    /**
     * Catch all events.
     *
     * @param object $event
     * @return bool
     */
    public static function tracked_events($event) {

        $data    = json_decode(json_encode($event->get_data()));
        $user    = \core_user::get_user($data->userid);
        $context = \context::instance_by_id($data->contextid);

        if (!has_capability('local/game:play', $context, $user)) {
            return false;
        }

        $game = new \local_game\game($context);
        if (!$game->init()) {
            return false;
        }

        if (!array_key_exists($data->eventname, $game->events)) {
            return false;
        }
        $gameevent = $game->events[$data->eventname];

        $game->add_points($user->id, $gameevent->points);

        return true;

    }
}