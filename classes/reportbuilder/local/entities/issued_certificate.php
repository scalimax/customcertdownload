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
namespace report_customcertdownload\reportbuilder\local\entities;

use core_reportbuilder\local\entities\base;
use core_reportbuilder\local\helpers\format;
use core_reportbuilder\local\report\column;
use core_reportbuilder\local\report\filter;
use core_reportbuilder\local\filters\date;
use core_reportbuilder\local\filters\text;
use core_reportbuilder\local\filters\boolean_select;
use stdClass;
use html_writer;
use lang_string;
use moodle_url;

class issued_certificate extends base {

    protected function get_default_tables(): array {
        return [
            'customcert_issues',
        ];
    }

    protected function get_default_entity_title(): lang_string {
        return new lang_string('customcertissues', 'report_customcertdownload');
    }

    public function initialise(): base {
        $columns = $this->get_all_columns();
        foreach ($columns as $column) {
            $this->add_column($column);
        }

        $filters = $this->get_all_filters();
        foreach ($filters as $filter) {
            $this->add_filter($filter);
        }

        return $this;
    }

    /*
+--------------+-------------+------+-----+---------+----------------+
| Field        | Type        | Null | Key | Default | Extra          |
+--------------+-------------+------+-----+---------+----------------+
| id           | bigint(20)  | NO   | PRI | NULL    | auto_increment |
| userid       | bigint(20)  | NO   | MUL | 0       |                |
| customcertid | bigint(20)  | NO   | MUL | 0       |                |
| code         | varchar(40) | YES  |     | NULL    |                |
| emailed      | tinyint(1)  | NO   |     | 0       |                |
| timecreated  | bigint(20)  | NO   |     | 0       |                |
+--------------+-------------+------+-----+---------+----------------+

    */

    protected function get_all_columns(): array {
        $tablealias = $this->get_table_alias('customcert_issues');

        $columns[] = (new column('id', 
            new lang_string('id', 'report_customcertdownload'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_fields("{$tablealias}.id");

        $columns[] = (new column('code', 
        new lang_string('code', 'report_customcertdownload'),
        $this->get_entity_name()
         ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$tablealias}.code, {$tablealias}.id, {$tablealias}.customcertid, {$tablealias}.userid")
            ->add_callback(static function(?string $value, stdClass $row): string {
                $cm = get_coursemodule_from_instance('customcert', $row->customcertid, 0, false);
                //http://localhost/lms/mod/customcert/view.php?id=69&downloadissue=3
                return html_writer::link(new moodle_url('/mod/customcert/view.php', ['id' => $cm->id, "downloadissue" => $row->userid]), $value);
            });
         
        $columns[] = (new column('emailed', 
        new lang_string('emailed', 'report_customcertdownload'),
        $this->get_entity_name()
            ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_BOOLEAN)
            ->add_fields("{$tablealias}.emailed")
            ->set_callback([format::class, 'boolean_as_text']);
            /*
->set_callback(static function(bool $success): string {
                if (!$success) {
                    return new lang_string('emailed_not_sent','report_customcertdownload');
                }
                return new lang_string('emailed_sent','report_customcertdownload');
            })
            */
//            ->set_callback([format::class, 'boolean_as_text']);

        $columns[] = (new column('timecreated', 
        new lang_string('timecreated', 'report_customcertdownload'),
        $this->get_entity_name()
            ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_INTEGER)
            ->add_fields("{$tablealias}.timecreated")
            ->set_is_sortable(true)
            ->add_callback([format::class, 'userdate']);
            
        return $columns;
    }

    protected function get_all_filters(): array {
        $tablealias = $this->get_table_alias('customcert_issues');

        $filters[] = (new filter(
            date::class,
            'timecreated',
            new lang_string('timecreated', 'report_customcertdownload'),
            $this->get_entity_name(),
            "{$tablealias}.timecreated"
        ))
        ->add_joins($this->get_joins())
        ->set_limited_operators([
            date::DATE_ANY,
            date::DATE_RANGE,
        ]);

        $filters[] = (new filter(
            boolean_select::class,
            'emailed',
            new lang_string('emailed', 'report_customcertdownload'),
            $this->get_entity_name(),
            "{$tablealias}.emailed"
        ))
        ->add_joins($this->get_joins());

        $filters[] = (new filter(
            text::class,
            'code',
            new lang_string('code', 'report_customcertdownload'),
            $this->get_entity_name(),
            "{$tablealias}.code"
        ))
        ->add_joins($this->get_joins());

        return $filters;
    }
}