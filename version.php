<?php
/**
 * Version info
 *
 * This File contains information about the current version of report/logs
 *
 * @package    report_customcertdownload
 * @copyright  2020 onwards Ardea s.r.l. (http://ardea.srl)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$plugin->version   = 2020112300;    // The current plugin version (Date: YYYYMMDDXX)
$plugin->requires  = 2019111800;    // Requires this Moodle version
$plugin->component = 'report_customcertdownload';  // Full name of the plugin (used for diagnostics)

$plugin->release   = "0.0.1"; // User-friendly version number.

$plugin->dependencies = array(
  'mod_customcert' => 2019111802
);
