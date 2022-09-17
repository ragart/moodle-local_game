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
 * Instance management.
 *
 * @package    local_game
 * @copyright  2022 Salvador Banderas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$contextid = required_param('contextid', PARAM_INT);
$context = context::instance_by_id($contextid, MUST_EXIST);

$PAGE->set_context($context);
$PAGE->set_url('/local/game/instance.php', array('contextid' => $contextid));

require_login();
require_capability('local/game:manage', $context);

$PAGE->set_title(get_string('pluginname', 'local_game'));
$PAGE->set_heading(get_string('pluginname', 'local_game'));
$PAGE->set_pagetype('local-game-edit-instance');

if ($context->contextlevel == CONTEXT_SYSTEM) {
    $PAGE->set_pagelayout('admin');
} elseif ($context->contextlevel == CONTEXT_COURSECAT) {
    $PAGE->set_pagelayout('coursecategory');
} elseif ($context->contextlevel == CONTEXT_COURSE
        and $context->instanceid != SITEID) {
    $course = $DB->get_record('course', array('id' => $context->instanceid), '*', MUST_EXIST);
    $PAGE->set_course($course);
    $PAGE->set_pagelayout('incourse');
} else {
    print_error('invalidcontext');
}

echo $OUTPUT->header();

$game = new \local_game\game($context);
$game->init();

echo $OUTPUT->footer();
