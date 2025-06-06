<?php



defined('MOODLE_INTERNAL') || die;

use lang_string;

$ADMIN->add('reports', new admin_externalpage('reportcustomcert_download', new lang_string('pluginname','report_customcertdownload'), "$CFG->wwwroot/report/customcertdownload/index.php"));

// no report settings
$settings = null;
