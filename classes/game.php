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
 * Game.
 *
 * @package    local_game
 * @copyright  2022 Salvador Banderas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_game;

defined('MOODLE_INTERNAL') || die();

/**
 * Game.
 *
 * @copyright  2022 Salvador Banderas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class game {

    /** @var int Identifier */
    protected $id;

    /** @var stdClass Context */
    protected $context;

    /** @var stdClass Active instance */
    protected $activeinstance;

    /** @var int Status */
    protected $status;

    /** @var array Events */
    public $events = [];

    /**
     * Constructor.
     *
     * @param stdClass $context Context
     * @return void
     */
    public function __construct($context) {
        $this->context = game_manager::get_game_context($context);
    }

    /**
     * Initiate the game instance.
     *
     * @return bool
     */
    public function init() {

        $instance = game_manager::check_instance_status($this->context);

        $this->id     = $instance->id;
        $this->status = $instance->status;

        if ($this->status == game_manager::STATUS_DISABLED) {
            return false;
        } elseif ($this->status == game_manager::STATUS_INHERIT) {
            if (!$this->activeinstance = game_manager::get_parent_instance($this->context)) {
                return false;
            }
        } elseif ($this->status == game_manager::STATUS_OWNINSTANCE) {
            $this->activeinstance = $instance;
        } else {
            return false;
        }

        $this->fetch_events();

        return true;
    }

    /**
     * Set the status for the game instance.
     *
     * @param int $status Status
     * @return void
     */
    public function set_status($status) {
        if (!in_array($status, [
                game_manager::STATUS_DISABLED,
                game_manager::STATUS_INHERIT,
                game_manager::STATUS_OWNINSTANCE])) {
            throw new \Exception('Invalid status');
        }
        $this->status = $status;
        game_manager::update_instance($this->id, $this->status);
    }

    /**
     * Fetch instance events from the database.
     *
     * @return void
     */
    public function fetch_events() {
        global $DB;
        if (empty($this->activeinstance->id)) {
            return false;
        }
        $events = $DB->get_records('local_game_events', ['instanceid' => $this->activeinstance->id]);
        foreach ($events as $event) {
            $this->events[$event->eventname] = $event;
        }
    }

    /**
     * Add event to game instance.
     *
     * @return object $event Event object
     */
    public function add_event($event) {
        global $DB;

        if (empty($this->activeinstance->id)
                or !$this->validate_event($event)) {
            return false;
        }

        $event->instanceid = $this->activeinstance->id;
        $event->timemodified = time();

        if (!$event->id = $DB->insert_record('local_game_events', $event)) {
            return false;
        }

        $this->events[$event->eventname] = $event;

        return $event;
    }

    /**
     * Validate the event object.
     *
     * @param object $event Event object
     * @return bool
     */
    private function validate_event($event) {
        if (empty($event->eventname)) {
            return false;
        }
        if (empty($event->points)
                or !is_numeric($event->points)
                or $event->points < 0) {
            return false;
        }
        if (empty($event->freqmax)
                or !is_numeric($event->freqmax)
                or $event->freqmax < 0) {
            return false;
        }
        if (empty($event->freqinterval)
                or !is_numeric($event->freqinterval)
                or $event->freqinterval < 0) {
            return false;
        }
        if (empty($event->freqtype)
                or !in_array($event->freqtype, [
                        game_manager::FREQ_ONCE,
                        game_manager::FREQ_DAILY,
                        game_manager::FREQ_WEEKLY,
                        game_manager::FREQ_MONTHLY,
                        game_manager::FREQ_YEARLY])) {
            return false;
        }
        return true;
    }

    /**
     * Remove event from the game instance.
     *
     * @return object $event Event object
     */
    public function remove_event($event) {
        global $DB;

        if (empty($this->activeinstance->id)) {
            return false;
        }

        if (!array_key_exists($event->eventname, $this->events)) {
            return false;
        }

        $DB->delete_records('local_game_events', ['id' => $this->events[$event->eventname]->id]);

        unset($this->events[$event->eventname]);

        return true;
    }

    /**
     * Get user current points.
     *
     * @param int $userid User identifier
     * @return int User points
     */
    public function get_points($userid) {
        global $DB;

        if (empty($this->activeinstance->id)) {
            return false;
        }

        if (!$points = $DB->get_record('local_game_points',
                ['instanceid' => $this->activeinstance->id, 'userid' => $userid])) {
            return 0;
        }

        return $points->points;
    }

    /**
     * Give points to user.
     *
     * @param int $userid User identifier
     * @param int $points Points to add
     * @return void
     */
    public function add_points($userid, $points) {
        global $DB;

        if (empty($this->activeinstance->id)) {
            return false;
        }

        if (!$currentpoints = $DB->get_record('local_game_points',
                ['instanceid' => $this->activeinstance->id, 'userid' => $userid])) {
            $currentpoints = new \stdClass();
            $currentpoints->instanceid = $this->activeinstance->id;
            $currentpoints->userid = $userid;
            $currentpoints->points = $points;
            $currentpoints->timemodified = time();
            $DB->insert_record('local_game_points', $currentpoints);
        } else {
            $currentpoints->points += $points;
            $currentpoints->timemodified = time();
            $DB->update_record('local_game_points', $currentpoints);
        }

    }

    /**
     * Substract points from user.
     *
     * @param int $userid User identifier
     * @param int $points Points to substract
     * @return void
     */
    public function substract_points($userid, $points) {
        global $DB;

        if (empty($this->activeinstance->id)) {
            return false;
        }

        if (!$currentpoints = $DB->get_record('local_game_points',
                ['instanceid' => $this->activeinstance->id, 'userid' => $userid])) {
            $currentpoints = new \stdClass();
            $currentpoints->instanceid = $this->activeinstance->id;
            $currentpoints->userid = $userid;
            $currentpoints->points = 0;
            $currentpoints->timemodified = time();
            $DB->insert_record('local_game_points', $currentpoints);
        } else {
            $currentpoints->points = max(0, $currentpoints->points - $points);
            $currentpoints->timemodified = time();
            $DB->update_record('local_game_points', $currentpoints);
        }

    }

    /**
     * Reset game for user.
     *
     * @param int $userid User identifier
     * @return void
     */
    public function reset_game($userid) {
        global $DB;

        if (empty($this->activeinstance->id)) {
            return false;
        }

        if (!$currentpoints = $DB->get_record('local_game_points',
                ['instanceid' => $this->activeinstance->id, 'userid' => $userid])) {
            $currentpoints = new \stdClass();
            $currentpoints->instanceid = $this->activeinstance->id;
            $currentpoints->userid = $userid;
            $currentpoints->points = 0;
            $currentpoints->timemodified = time();
            $DB->insert_record('local_game_points', $currentpoints);
        } else {
            $currentpoints->points = 0;
            $currentpoints->timemodified = time();
            $DB->update_record('local_game_points', $currentpoints);
        }

        $DB->delete_records('local_game_events_log', ['instanceid' => $this->activeinstance->id, 'userid' => $userid]);

    }

    /**
     * Log event.
     *
     * @param int $userid User identifier
     * @param int $points Points to add
     * @param string $eventname Event name
     * @param int $usermodified User who modified the points
     * @return bool
     */
    public function log($userid, $points, $eventname = null, $usermodified = null) {
        global $DB;

        if (empty($this->activeinstance->id)) {
            return false;
        }

        $log = new \stdClass();
        $log->time = time();
        $log->userid = $userid;
        $log->instanceid = $this->activeinstance->id;
        $log->eventname = $eventname;
        $log->usermodified = $usermodified;
        $log->points = $points;

        $DB->insert_record('local_game_points_log', $log);

        return true;
    }

    /**
     * Check if user has reached the frequency limit for an event.
     *
     * @param int $userid User identifier
     * @param string $eventname Event name
     * @return bool
     */
    public function check_frequency_limit($userid, $eventname) {
        global $DB;

        if (empty($this->activeinstance->id)) {
            return false;
        }

        if (!array_key_exists($eventname, $this->events)) {
            return false;
        }

        $event = $this->events[$eventname];

        $datetime = new \DateTime();
        switch ($event->freqtype) {
            case game_manager::FREQ_ONCE:
                $time = 0;
                break;
            case game_manager::FREQ_HOURLY:
                $datetime->sub(new \DateInterval('PT' . ($event->freqinterval - 1) . 'H'));
                $datetime->setTime((int)$datetime->format('H'), 0, 0);
                $time = $datetime->getTimestamp();
                break;
            case game_manager::FREQ_DAILY:
                $datetime->sub(new \DateInterval('P' . ($event->freqinterval - 1) . 'D'));
                $datetime->setTime(0, 0, 0);
                $time = $datetime->getTimestamp();
                break;
            case game_manager::FREQ_WEEKLY:
                $datetime->sub(new \DateInterval('P' . ($event->freqinterval - 1) . 'W'));
                $datetime->setISODate((int)$datetime->format('o'), (int)$datetime->format('W'), 1);
                $datetime->setTime(0, 0, 0);
                $time = $datetime->getTimestamp();
                break;
            case game_manager::FREQ_MONTHLY:
                $datetime->sub(new \DateInterval('P' . ($event->freqinterval - 1) . 'M'));
                $datetime->setDate((int)$datetime->format('Y'), (int)$datetime->format('m'), 1);
                $datetime->setTime(0, 0, 0);
                $time = $datetime->getTimestamp();
                break;
            case game_manager::FREQ_YEARLY:
                $datetime->sub(new \DateInterval('P' . ($event->freqinterval - 1) . 'Y'));
                $datetime->setDate((int)$datetime->format('Y'), 1, 1);
                $datetime->setTime(0, 0, 0);
                $time = $datetime->getTimestamp();
                break;
            default:
                return false;
        }

        if ($logs = $DB->get_records_select('local_game_points_log',
                'instanceid = :instanceid AND userid = :userid AND eventname = :eventname AND time >= :time',
                [
                    'instanceid' => $this->activeinstance->id,
                    'userid' => $userid,
                    'eventname' => $event->eventname,
                    'time' => $time
                ])
                and count($logs) >= $event->freqmax) {
            return false;
        }

        return true;
    }

}
