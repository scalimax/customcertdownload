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
use core_reportbuilder\local\report\column;

use lang_string;

class customcert extends base {

    protected function get_default_tables(): array {
        return [
            'customcert',
        ];
    }

    protected function get_default_entity_title(): lang_string {
        return new lang_string('customcert', 'report_customcertdownload');
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

    protected function get_all_columns(): array {
        $tablealias = $this->get_table_alias('customcert');

        $columns[] = (new column('name', 
            new lang_string('customcert_name', 'report_customcertdownload'),
            $this->get_entity_name()
        ))
            ->add_joins($this->get_joins())
            ->set_type(column::TYPE_TEXT)
            ->add_fields("{$tablealias}.name");

        return $columns;

    }

    protected function get_all_filters(): array {
        return array();
    }
}