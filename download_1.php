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

$id = required_param('id', PARAM_INT);
$from = required_param('from', PARAM_TEXT);
$to = required_param('to', PARAM_TEXT);

//var_dump($from);
//var_dump($to);

$cm = get_coursemodule_from_id('customcert', $id, 0, false, MUST_EXIST);
$customcert = $DB->get_record('customcert', array('id' => $cm->instance), '*', MUST_EXIST);

// Ensure the user is allowed to view this page.
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
require_login($course, false, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/customcert:view', $context);
require_capability('mod/customcert:receiveissue', $context);
require_capability('mod/customcert:manage', $context);
require_capability('mod/customcert:viewreport', $context);

//$canreceive = has_capability('mod/customcert:receiveissue', $context);
//$canmanage = has_capability('mod/customcert:manage', $context);
//$canviewreport = has_capability('mod/customcert:viewreport', $context);

//echo $customcert->id;

//$issues = $DB->get_records('customcert_issues', array('customcertid' => $customcert->id));
$issues = $DB->get_records_select('customcert_issues', 
        "customcertid = $customcert->id and timecreated between unix_timestamp('$from') and unix_timestamp('$to')");
//        "customcertid = $customcert->id");
if (empty($issues)) {
    echo "Nessun attestato emesso dal $from al $to";
    exit();
}
//$first_index = array_keys($issues)[0];
//echo var_dump($userid);

\core\session\manager::write_close();

// Now we want to generate the PDF.
$template = $DB->get_record('customcert_templates', array('id' => $customcert->templateid), '*', MUST_EXIST);
$template = new \mod_customcert\template($template);

$factory = new \report_customcertdownload\tools\ZipArchiveFactory();


foreach($issues as $value) {
    $userid = $value->userid;
    $user = $DB->get_record('user', array('id' => $userid));
    $filename = "$user->lastname $user->firstname $value->code";
    $factory->append("$filename.pdf", $template->generate_pdf(false, $userid, true));
}

$factory->close();

sendFile($factory->file(), "$course->shortname $from $to");

$factory->clean();
exit();


function sendFile($zipFile, $filename) {
    header("Content-type: application/zip");
    header("Content-Disposition: attachment; filename=$filename.zip");
    header("Content-length: " . filesize($zipFile));
    header("Pragma: no-cache");
    header("Expires: 0");
    readfile($zipFile);
}