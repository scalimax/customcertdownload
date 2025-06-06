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