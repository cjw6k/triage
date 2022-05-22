<?php

/**
 * The Reporter class is herein defined.
 *
 * @link https://triage.cjwillcock.ca/
 */

declare(strict_types=1);

namespace Triage\Triage;

use function array_keys;
use function ksort;
use function str_repeat;
use function strlen;

use const PHP_EOL;

/**
 * The Reporter class outputs a report from an Analysis
 */
class Reporter
{
    /**
     * Show cowspeak for display of error messages
     */
    private bool $_show_cows = false;

    /**
     * Show all the files scanned no matter what MIME type they have
     */
    private bool $_show_all_files = false;

    /**
     * Capture the options to show all the files in the scan, and to show cows
     *
     * @param bool $show_all_files The option flag.
     * @param bool $show_cows The option flag.
     */
    public function __construct(bool $show_all_files, bool $show_cows)
    {
        $this->_show_all_files = $show_all_files;
        $this->_show_cows = $show_cows;
    }

    /**
     * Output a report from an Analysis
     *
     * @param Analysis $analysis The analysis of the SOURCE.
     */
    public function report(Analysis $analysis): void
    {
        $directories = $analysis->getDirectoryScanCount();
        echo "$directories director", $this->_pluralize($directories, 'y', 'ies'), PHP_EOL;

        $files = $analysis->getFileScanCount();
        echo "$files file", $this->_pluralize($files), PHP_EOL;

        $details = $analysis->getDetails();
        ksort($details);

        foreach ($details as $mime_type => $files_of_type) {
            if ($this->_skipMimeType($mime_type)) {
                continue;
            }

            $this->_showMimeTypeDetails($mime_type);

            switch ($mime_type) {
                case 'text/css':
                    $this->_showCssFileAnalyses($files_of_type);

                    break;

                default:
                    $this->_showFileList($files_of_type);
            }
        }
    }

    /**
     * Provide singular or plural suffixes based on count
     *
     * @param int $count The quantity of the item.
     * @param string $singular The singular suffix.
     * @param string $plural The plural suffix.
     *
     * @return string The appropriate suffix
     */
    private function _pluralize(int $count, string $singular = '', string $plural = 's'): string
    {
        return ($count == 1) ? $singular : $plural;
    }

    /**
     * Skip reporting some files, based on MIME type
     *
     * @param string $mime_type The MIME type of the file.
     *
     * @return bool true Do not report on the files of this MIME type.
 * false Do report on the files of this MIME type.
     */
    private function _skipMimeType(string $mime_type): bool
    {
        if ($this->_show_all_files) {
            return false;
        }

        switch ($mime_type) {
            case 'text/css':
                return false;

            default:
                return true;
        }
    }

    /**
     * Output a formatted string with the given MIME type
     *
     * @param string $mime_type The MIME type to display.
     */
    private function _showMimeTypeDetails(string $mime_type): void
    {
        echo PHP_EOL, "===== $mime_type =====", PHP_EOL;
    }

    /**
     * Output the name and scan path of each file
     *
     * @param array<mixed> $files_of_type The set of files.
     */
    private function _showFileList(array $files_of_type): void
    {
        foreach (array_keys($files_of_type) as $scan_path) {
            echo " $scan_path", PHP_EOL;
        }
    }

    /**
     * Output the result of analysis on the CSS files
     *
     * @param array<mixed> $files_of_type The detailed analyses.
     */
    private function _showCssFileAnalyses(array $files_of_type): void
    {
        $css_reporter = new Reporter\Css($this->_show_cows);

        // later: new Reporter\Table, etc, etc
        foreach ($files_of_type as $scan_path => $analysis) {
            echo "+", str_repeat('-', strlen($scan_path) + 4), "+", PHP_EOL;
            echo "|  $scan_path  |", PHP_EOL;
            echo "+", str_repeat('-', strlen($scan_path) + 4), "+", PHP_EOL;

            $css_reporter->report($analysis);
        }

        $css_reporter->summary();
    }
}
