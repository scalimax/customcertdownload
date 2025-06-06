<?php

// This file is part of the report_customcertdownload module for Moodle - http://moodle.org/
//
// This plugin is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This plugin is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this plugin.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package    report_customcertdownload
 * @copyright  2025 onwards Massimo Scali <massimo.scali@ardea.srl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');

$pageurl = new moodle_url('/report/customcertdownload/timing_report.php');
require_login($course, false, $cm);
$context = context_system::instance();
require_capability('mod/customcert:view', $context);

$canreceive = has_capability('mod/customcert:receiveissue', $context);
$canmanage = has_capability('mod/customcert:manage', $context);
$canviewreport = has_capability('mod/customcert:viewreport', $context);

global $SITE;
// Initialise $PAGE.
$PAGE->set_url($pageurl);
$PAGE->set_context($context);
$PAGE->set_title("$SITE->fullname - Resoconto attestati non conformi");

$PAGE->set_pagelayout('admin');
$PAGE->set_heading("$SITE->fullname - Resoconto attestati non conformi");

echo $OUTPUT->header();

$certificates = $DB->get_records('customcert');

foreach ($certificates as $customcert) {
    $id = $customcert->id;
    //$cm_from_certificate = $DB->get_record('course_modules', array('instance' => $id));

    $cm = get_coursemodule_from_instance('customcert', $id, 0, false, MUST_EXIST);

    //if (!$cm) {continue;}

    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    //$customcert = $DB->get_record('customcert', array('id' => $cm->instance), '*', MUST_EXIST);
// Ensure the user is allowed to view this page.
    //\mod_customcert\page_helper::page_setup($pageurl, $context, "format_string($course->fullname)");
//    $event = \mod_customcert\event\course_module_viewed::create(array(
//                'objectid' => $customcert->id,
//                'context' => $context,
//    ));
//    $event->add_record_snapshot('course', $course);
//    $event->add_record_snapshot('customcert', $customcert);
//    $event->trigger();
    // Get the current groups mode.
    if ($groupmode = groups_get_activity_groupmode($cm)) {
        groups_get_activity_group($cm, true);
    }

    // Generate the table to the report if there are issues to display.
    if ($canviewreport) {
        // Get the total number of issues.
        $reporttable = new \report_customcertdownload\timing_report_table($customcert->id, $cm, $groupmode, $downloadtable);
        $reporttable->define_baseurl($pageurl);
        $reporttable->pageable(false);
    }

    // Generate the intro content if it exists.
    $intro = '';
    if (!empty($customcert->intro)) {
        $intro = $OUTPUT->box(format_module_intro('customcert', $customcert, $cm->id), 'generalbox', 'intro');
    }

    // If the current user has been issued a customcert generate HTML to display the details.
    $issuehtml = '';
    $issues = $DB->get_records('customcert_issues', array('userid' => $USER->id, 'customcertid' => $customcert->id));
    if ($issues && !$canmanage) {
        // Get the most recent issue (there should only be one).
        $issue = reset($issues);
        $issuestring = get_string('receiveddate', 'customcert') . ': ' . userdate($issue->timecreated);
        $issuehtml = $OUTPUT->box($issuestring);
    }

    // Create the button to download the customcert.
    // Output all the page data.
    echo $OUTPUT->heading(format_string("$course->fullname ($course->shortname)"));
    echo $intro;
    echo $issuehtml;
    echo $downloadbutton;

    //$numissues = \mod_customcert\certificate::get_number_of_issues($customcert->id, $cm, $groupmode);
    //echo $OUTPUT->heading(get_string('listofissues', 'customcert', $numissues), 3);
    //groups_print_activity_menu($cm, $pageurl);
    echo $reporttable->out($perpage, false);
}
echo $OUTPUT->footer();
exit();
