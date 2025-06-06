<?php

use context_system;
use lang_string;
use core_reportbuilder\system_report_factory;
use report_customcertdownload\reportbuilder\local\systemreports\issued_certificates;

require(__DIR__.'/../../config.php');

echo $OUTPUT->header();
echo $OUTPUT->heading(new lang_string('title', 'report_customcertdownload'));

$report = system_report_factory::create(issued_certificates::class, context_system::instance());
echo $report->output();

echo $OUTPUT->footer();