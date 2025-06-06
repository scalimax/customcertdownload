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
 * The report that displays issued certificates.
 *
 * @package    mod_customcert
 * @copyright  2016 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace report_customcertdownload;

defined('MOODLE_INTERNAL') || die;

global $CFG;

require_once($CFG->libdir . '/tablelib.php');

/**
 * Class for the report that displays issued certificates.
 *
 * @package    mod_customcert
 * @copyright  2016 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class timing_report_table extends \table_sql {

    /**
     * @var int $customcertid The custom certificate id
     */
    protected $customcertid;

    /**
     * @var \stdClass $cm The course module.
     */
    protected $cm;

    /**
     * @var bool $groupmode are we in group mode?
     */
    protected $groupmode;
    
    private $showOnlyNonCompliant;

    /**
     * Sets up the table.
     *
     * @param int $customcertid
     * @param \stdClass $cm the course module
     * @param bool $groupmode are we in group mode?
     * @param string|null $download The file type, null if we are not downloading
     */
    public function __construct($customcertid, $cm, $groupmode, $showOnlyBad = null) {
        parent::__construct('mod_customcert_report_table');

        $context = \context_module::instance($cm->id);
        $extrafields = get_extra_user_fields($context);

        $columns = [];
        $columns[] = 'course';
        $columns[] = 'requiredtime';
        $columns[] = 'totaltime';
        $columns[] = 'fullname';
        foreach ($extrafields as $extrafield) {
            $columns[] = $extrafield;
        }
        $columns[] = 'timecreated';
        $columns[] = 'code';

        $headers = [];
        $headers[] = 'Course';
        $headers[] = 'Required time';
        $headers[] = 'Actual time';
        $headers[] = get_string('fullname');
        foreach ($extrafields as $extrafield) {
            $headers[] = get_user_field_name($extrafield);
        }
        $headers[] = get_string('receiveddate', 'customcert');
        $headers[] = get_string('code', 'customcert');


        $this->define_columns($columns);
        $this->define_headers($headers);
        $this->collapsible(false);
        $this->sortable(true);
        $this->no_sorting('course');
        $this->no_sorting('code');
        $this->no_sorting('totaltime');
        $this->no_sorting('requiredtime');
        $this->is_downloadable(false);

        $this->customcertid = $customcertid;
        $this->cm = $cm;
        $this->groupmode = $groupmode;
        
        global $DB;
        $customcert = $DB->get_record('customcert', array('id' => $cm->instance), '*', MUST_EXIST);
        $this->requiredtime = $customcert->requiredtime;
        $this->showOnlyNonCompliant = true;
    }

    /**
     * Generate the fullname column.
     *
     * @param \stdClass $user
     * @return string
     */
    public function col_fullname($user) {
        global $OUTPUT;

        if (!$this->is_downloading()) {
            return $OUTPUT->user_picture($user) . ' ' . fullname($user);
        } else {
            return fullname($user);
        }
    }
    
    /**
     * Generate the certificate time created column.
     *
     * @param \stdClass $user
     * @return string
     */
    public function col_timecreated($user) {
        return userdate($user->timecreated);
    }

    /**
     * Generate the code column.
     *
     * @param \stdClass $user
     * @return string
     */
    public function col_code($user) {
        return $user->code;
    }
    
        /**
     * Generate the certificate total time.
     *
     * @param \stdClass $user
     * @return string
     */
    public function col_totaltime($user) {
        $totaltime = \mod_customcert\certificate::get_course_time($this->cm->course, $user->id);
        $result = $this->format_duration($totaltime);
        if ($this->showOnlyNonCompliant) return $result;
        
        if ($totaltime < $this->requiredtime*60) {
            return '=>>>  '.$result.'  <<<=';
        }
    }

    public function col_course($user) {
        global $DB;
        $course = $DB->get_record("course", array('id' => $this->cm->course));
        return "$course->fullname ($course->shortname)";
    }


    /**
     * Generate the certificate requested time column.
     *
     * @param \stdClass $user
     * @return string
     */
    public function col_requiredtime($user) {
        return $this->format_duration($this->requiredtime*60);
        //return $this->requiredtime;
        //return $this->requiredtime/60; //get_time_interval_string(0, $this->requiredtime);
    }
    
    
    
    private function format_duration($timeinterval) {
        $dt = new \DateTime();
        $dt->add(new \DateInterval('PT'.($timeinterval+1).'S'));
        $interval = $dt->diff(new \DateTime());
        return $interval->format('%Hh %Im'); // %Ss
        
    }



    /**
     * Query the reader.
     *
     * @param int $pagesize size of page for paginated displayed table.
     * @param bool $useinitialsbar do you want to use the initials bar.
     */
    public function query_db($pagesize, $useinitialsbar = true) {
        global $DB;
        $total = \mod_customcert\certificate::get_number_of_issues($this->customcertid, $this->cm, $this->groupmode);

        $this->pagesize($pagesize, $total);

        $array = \mod_customcert\certificate::get_issues($this->customcertid, $this->groupmode, $this->cm,
            $this->get_page_start(), $this->get_page_size(), $this->get_sql_sort());
        
        foreach($array as $i => $row) {
            $userid = $DB->get_record('customcert_issues', array('id' => $row->issueid), '*', MUST_EXIST)->userid;
            $row->totaltime = \mod_customcert\certificate::get_course_time($this->cm->course, $userid);
            if ($this->showOnlyNonCompliant && $row->totaltime >= $this->requiredtime*60) {
                unset($array[$i]);
            }
        }
        $this->rawdata = array_values($array);
        
        // Set initial bars.
        if ($useinitialsbar) {
            $this->initialbars($total > $pagesize);
        }
    }

    /**
     * Download the data.
     */
    public function download() {
        \core\session\manager::write_close();
        $total = \mod_customcert\certificate::get_number_of_issues($this->customcertid, $this->cm, $this->groupmode);
        $this->out($total, false);
        exit;
    }
}

