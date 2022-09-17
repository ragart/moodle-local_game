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
 * Game manager.
 *
 * @package    local_game
 * @copyright  2022 Salvador Banderas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_game;

defined('MOODLE_INTERNAL') || die();

/**
 * Game manager.
 *
 * @copyright  2022 Salvador Banderas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class game_manager {

    /** 
     * Used to limit the maximum number of points that can be awarded for a single action.
     * Just one time.
     */
    const FREQ_ONCE = 1;

    /**
     * Used to limit the maximum number of points that can be awarded for a single action.
     * Per hour.
     */
    const FREQ_HOURLY = 2;

    /**
     * Used to limit the maximum number of points that can be awarded for a single action.
     * Per day.
     */
    const FREQ_DAILY = 4;

    /**
     * Used to limit the maximum number of points that can be awarded for a single action.
     * Per week.
     */
    const FREQ_WEEKLY = 8;

    /**
     * Used to limit the maximum number of points that can be awarded for a single action.
     * Per month.
     */
    const FREQ_MONTHLY = 16;

    /**
     * Used to limit the maximum number of points that can be awarded for a single action.
     * Per year.
     */
    const FREQ_YEARLY = 32;

    /**
     * Used to define the status of a game instance.
     * Disabled.
     */
    const STATUS_DISABLED = 0;

    /**
     * Used to define the status of a game instance.
     * Inherit from parent context.
     */
    const STATUS_INHERIT = 1;

    /**
     * Used to define the status of a game instance.
     * Own instance.
     */
    const STATUS_OWNINSTANCE = 2;

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
     * @return core_plugin_manager The Singleton instance.
     */
    public static function instance() {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Get the game context for non-game contexts.
     *
     * @return stdClass
     */
    public static function get_game_context($context) {
        if ($context->contextlevel > CONTEXT_COURSE
                or $context->contextlevel == CONTEXT_USER) {
            $context = $context->get_parent_context();
            return self::get_game_context($context);
        }
        return $context;
    }

    /**
     * Check the existence and status of an instance.
     * It populates the database if the instance does not exist.
     *
     * @param stdClass $context Context
     * @return object Instance
     */
    public static function check_instance_status($context) {
        global $DB;

        if ($instance = $DB->get_record('local_game_instances', ['contextid' => $context->id])) {
            if ($context->contextlevel == CONTEXT_SYSTEM) {
                $enableglobalinstance = get_config('local_game', 'enableglobalinstance');
                if (!$enableglobalinstance
                        and $instance->status != self::STATUS_DISABLED) {
                    $instance->status = self::STATUS_DISABLED;
                    self::update_instance($instance->id, $instance->status);
                } elseif ($enableglobalinstance
                        and $instance->status == self::STATUS_DISABLED) {
                    $instance->status = self::STATUS_OWNINSTANCE;
                    self::update_instance($instance->id, $instance->status);
                }
            }
        } else {
            $instance            = new \stdClass();
            $instance->contextid = $context->id;
            $instance->status    = self::STATUS_INHERIT;
            if ($context->contextlevel == CONTEXT_SYSTEM) {
                if (!get_config('local_game', 'enableglobalinstance')) {
                    $instance->status = self::STATUS_DISABLED;
                } else {
                    $instance->status = self::STATUS_OWNINSTANCE;
                }
            }
            if (!$instance->id = self::create_instance($instance->contextid, $instance->status)) {
                return false;
            }
        }

        return $instance;
    }

    /**
     * Get the parent instance.
     *
     * @return stdClass Parent instance
     */
    public static function get_parent_instance($context) {

        $parentcontext = $context->get_parent_context();
        $parentinstance = self::check_instance_status($parentcontext);

        if ($parentinstance->status == self::STATUS_DISABLED) {
            return false;
        }
        if ($parentinstance->status == self::STATUS_INHERIT) {
            return self::get_parent_instance($parentcontext);
        }
        if ($parentinstance->status == self::STATUS_OWNINSTANCE) {
            return $parentinstance;
        }

    }

    /**
     * Create the game instance in the database.
     *
     * @param int $contextid Context identifier
     * @param int $status Status
     * @return stdClass Game instance
     */
    public static function create_instance($contextid, $status) {
        global $DB;

        $game               = new \stdClass();
        $game->contextid    = $contextid;
        $game->status       = $status;
        $game->timemodified = time();

        if (!$game->id = $DB->insert_record('local_game_instances', $game)) {
            return false;
        }

        return $game->id;
    }

    /**
     * Update the game instance in the database.
     *
     * @param int $instanceid Instance identifier
     * @param int $status Status
     * @return stdClass Game instance
     */
    public static function update_instance($instanceid, $status) {
        global $DB;

        $game               = new \stdClass();
        $game->id           = $instanceid;
        $game->status       = $status;
        $game->timemodified = time();

        $DB->update_record('local_game_instances', $game);

        return $game;
    }

    /**
     * Delete the game instance from the database.
     *
     * @return void
     */
    public static function delete_instance($instanceid) {
        global $DB;

        $DB->delete_records('local_game_instances', ['id' => $instanceid]);
    }

}
