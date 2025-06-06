<?php

/*
 * Twine &egrave; un plug in di Moodle per integrare Twine e Moodle.
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
