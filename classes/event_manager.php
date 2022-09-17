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
 * Event manager.
 *
 * @package    local_game
 * @copyright  2022 Salvador Banderas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_game;

defined('MOODLE_INTERNAL') || die();

use core_component,
    ReflectionClass;

/**
 * Event manager.
 *
 * @copyright  2022 Salvador Banderas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class event_manager {

    /** @var event_manager Singleton instance */
    protected static $instance;

    /**
     * Avoid direct instantiation.
     */
    protected function __construct() {
    }

    /**
     * Avoid cloning.
     */
    protected function __clone() {
    }

    /**
     * Factory method.
     *
     * @return event_manager The Singleton instance.
     */
    public static function instance() {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }
 
    /**
     * Get a list of all events.
     *
     * @return  array
     */
    public static function get_event_list($ignorelist = [], $edulevel = []) : array {
        global $CFG;

        $debuglevel          = $CFG->debug;
        $debugdisplay        = $CFG->debugdisplay;
        $debugdeveloper      = $CFG->debugdeveloper;
        $CFG->debug          = 0;
        $CFG->debugdisplay   = false;
        $CFG->debugdeveloper = false;

        $ignoreevents = [
            \core\event\unknown_logged::class,
            \logstore_legacy\event\legacy_logged::class,
        ];
        $ignoreevents = array_unique(array_merge($ignoreevents, $ignorelist));

        $eventlist = [];

        $events = core_component::get_component_classes_in_namespace(null, 'event');
        foreach (array_keys($events) as $eventname) {
            if (is_a($eventname, \core\event\base::class, true)
                    && !in_array($eventname, $ignoreevents)) {
                $reflectionClass = new ReflectionClass($eventname);
                if (!$reflectionClass->isAbstract()) {
                    $eventfullname = "\\${eventname}";
                    $event = (object) $eventfullname::get_static_info();
                    if (!empty($edulevel)
                            and !in_array($event->edulevel, $edulevel)) {
                        continue;
                    }
                    $event->fullname = $eventfullname::get_name();
                    $stringmanager = get_string_manager();
                    $event->componentfullname = $event->component;
                    if ($stringmanager->string_exists('pluginname', $event->component)) {
                        $event->componentfullname = get_string('pluginname', $event->component);
                    }
                    $eventlist[$eventname] = $event;
                }
            }
        }

        $CFG->debug          = $debuglevel;
        $CFG->debugdisplay   = $debugdisplay;
        $CFG->debugdeveloper = $debugdeveloper;

        return $eventlist;

    }

    /**
     * Get a list of game related events.
     *
     * @return  array
     */
    public static function get_game_event_list() {
        $ignoredevents = [
            \core\event\course_module_completion_updated::class,
            \core\event\course_completion_updated::class
        ];
        $edulevels = [
            \core\event\base::LEVEL_TEACHING,
            \core\event\base::LEVEL_PARTICIPATING
        ];
        return self::get_event_list($ignoredevents, $edulevels);
    }

}
