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
namespace report_customcertdownload\tools;

use \ZipArchive;
/**
 * Description of ZipArchiveFactory
 *
 * @author moodle
 */
class ZipArchiveFactory {

    private $zipFile;
    private $archive;

    //put your code here
    function __construct() {

// Create new ZIP file and open it for writing
        $this->openTempFile();

// Try opening the ZIP file
        $this->openZipArchive();
    }
    
    public function append($filename, $content) {
        $this->archive->addFromString($filename, $content);
    }
    
    public function close() {
        $this->archive->close();
    }
    
    public function clean() {
        unlink($this->zipFile);
    }
    
    public function file() {
        return $this->zipFile;
    }

    private function openTempFile() {
        // Create new ZIP file and open it for writing
        $this->zipFile = tempnam(sys_get_temp_dir(), 'data');

        if (file_exists($this->zipFile)) {
            unlink($this->zipFile);
        }
    }

    private function openZipArchive() {
        // Try opening the ZIP file
        $this->archive = new ZipArchive();
        if ($this->archive->open($this->zipFile, ZipArchive::OVERWRITE) !== true) {
            if ($this->archive->open($this->zipFile, ZipArchive::CREATE) !== true) {
                throw new WriterException("Could not open $this->zipFile for writing.");
            }
        }
    }

}
