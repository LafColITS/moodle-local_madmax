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
 * Reset minmaxtouse sitewide.
 *
 * @package   local_madmax
 * @copyright 2015 Collaborative Liberal Arts Moodle Project
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/../../grade/lib.php');
require_once($CFG->libdir . '/adminlib.php');

$fix = optional_param('fix', 0, PARAM_INT);

require_login();

// Setup page.
$context = context_system::instance();
$PAGE->set_url('/local/madmax/index.php');
$PAGE->set_pagelayout('report');
$PAGE->set_context($context);

// Check permissions.
require_capability('local/madmax:view', $context);

// Finish setting up page.
$PAGE->set_title('Reset minmax');
$PAGE->set_heading(get_string('pluginname', 'local_madmax'));

// Get courses.
$settings = $DB->get_records_select('grade_settings', "value<>$CFG->grade_minmaxtouse");
if($fix) {
    switch($CFG->grade_minmaxtouse) {
        case GRADE_MIN_MAX_FROM_GRADE_GRADE:
            $upgradefunction = 'grade_upgrade_use_min_max_from_grade_grade';
            break;
        case GRADE_MIN_MAX_FROM_GRADE_ITEM:
            $upgradefunction = 'grade_upgrade_use_min_max_from_grade_item';
            break;
    }
    foreach($settings as $setting) {
        $upgradefunction($setting->courseid);
        grade_hide_min_max_grade_upgrade_notice($setting->courseid);
    }
    notice(get_string("updated","local_madmax",count($settings)), new moodle_url('/local/madmax/index.php'));
}

// Display the page.
admin_externalpage_setup('local_madmax');
echo $OUTPUT->header();
$html = html_writer::start_tag('div');
$html .= html_writer::tag('span', get_string('foundcourses', 'local_madmax', count($settings)));
$html .= $OUTPUT->single_button(new moodle_url('/local/madmax/index.php', array('fix' => 1)), get_string('continue'), 'post');
$html .= html_writer::end_tag('div');
echo $html;
echo $OUTPUT->footer();
