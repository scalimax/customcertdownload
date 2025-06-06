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
namespace report_customcertdownload\reportbuilder\local\systemreports;

use context_system;
use core_reportbuilder\system_report;
use report_customcertdownload\reportbuilder\local\entities\issued_certificate;
use report_customcertdownload\reportbuilder\local\entities\customcert;
use core_reportbuilder\local\entities\user;
use core_reportbuilder\local\entities\course;

class issued_certificates extends system_report {
    
    protected function initialise(): void {
        $courseentity = new course();
        $this->add_entity($courseentity);
        
        //$contextalias = $courseentity->get_table_alias('context');
        $coursealias = $courseentity->get_table_alias('course');
        $this->set_main_table('course', $coursealias);

        $certificateentity = new customcert();
        $certificatealias = $certificateentity->get_table_alias('customcert');
        $this->add_entity($certificateentity->add_join("LEFT JOIN {customcert} {$certificatealias} ON {$certificatealias}.course = {$coursealias}.id")); 

        $issuedcertificateentity = new issued_certificate();
        $issuedcertificatealias = $issuedcertificateentity->get_table_alias('customcert_issues');

        // $this->set_main_table('customcert_issues', $issuedcertificateentity);
        $this->add_entity($issuedcertificateentity
            ->add_join("LEFT JOIN {customcert_issues} {$issuedcertificatealias} ON {$issuedcertificatealias}.customcertid = {$certificatealias}.id")
        );

        $entityuser = new user();
        $entityuseralias = $entityuser->get_table_alias('user');
        $this->add_entity($entityuser->add_join("LEFT JOIN {user} {$entityuseralias} ON {$entityuseralias}.id = {$issuedcertificatealias}.userid"));

        $this->add_columns();
        $this->add_filters();
    }

    /**
     * Validates access to view this report
     *
     * @return bool
     */
    protected function can_view(): bool {
        return has_capability('mod/customcert:viewallcertificates', context_system::instance());
    }

    protected function add_columns(): void {
        $columns = [
            'course:fullname',
            'customcert:name',
            'issued_certificate:id',
            'user:fullnamewithlink',
            'issued_certificate:code',
            'issued_certificate:emailed',
            'issued_certificate:timecreated',

        ];

        $this->add_columns_from_entities($columns);
        $this->set_initial_sort_column('issued_certificate:timecreated', SORT_DESC);

    }

    protected function add_filters(): void {
        $filters = [
            'course:fullname',
            'user:fullname',
            'issued_certificate:timecreated',
            'issued_certificate:emailed',
            'issued_certificate:code',
        ];

        $this->add_filters_from_entities($filters);
    }
}