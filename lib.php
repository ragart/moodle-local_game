<?php

function local_game_extend_settings_navigation($settingsnav, $context) {
    global $CFG;
    if (($context->contextlevel === 50) &&
            has_capability('local/game:manage', $context)){
        $url = new moodle_url($CFG->wwwroot . '/local/game/instance.php',
            ['contextid' => $context->id]);
        if (!$coursesettingsnode = $settingsnav->find('courseadmin', null)) {
            return false;
        }
        $node = $coursesettingsnode->create(
            new lang_string('pluginname', 'local_game'),
            $url,
            navigation_node::NODETYPE_LEAF,
            null,
            'game',
            new pix_icon('i/pad', 'game'));
        $coursesettingsnode->add_node($node, 'editsettings');
    }
}

