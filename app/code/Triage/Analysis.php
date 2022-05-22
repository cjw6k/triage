<?php

/**
 * The Analysis class is herein defined.
 *
 * @link https://triage.cjwillcock.ca/
 */

declare(strict_types=1);

namespace Triage\Triage;

/**
 * The Analysis class is a structured account of the syntax accuracy,
 * and semantic compatibility of the files in SOURCE.
 */
class Analysis
{
    /**
     * The account of analysis done on SOURCE as a whole
     *
     * @var array<mixed>
     */
    private array $_package = [
        'directories' => [],
        'files' => 0,
        'notices' => 0,
        'errors' => 0,
        'warnings' => 0,
        'css' => [],
        'js' => [],
        'html' => [],
        'php' => [],
    ];

    /**
     * The detailed account of analysis done on each file in SOURCE
     *
     * @var array<mixed>
     */
    private array $_details = [];

    /**
     * Capture the status of files and directories considered by the Picker
     *
     * @param array<mixed> $status The count of directories and files analyzed.
     */
    public function setPickerStatus(array $status): void
    {
        $this->_package['directories'] = $status['directories'];
        $this->_package['files'] = $status['files'];
    }

    /**
     * Recorded the detailed analysis of a file with a generic MIME type
     *
     * @param array<mixed> $file The detailed analysis.
     */
    public function addUnsupportedFile(array $file): void
    {
        $this->_details[$file['mime_type']][$file['scan_path']] = $file['filename'];
    }

    /**
     * Provides the number of directories scanned by the Picker
     *
     * @return int The number of directories
     */
    public function getDirectoryScanCount(): int
    {
        return $this->_package['directories'];
    }

    /**
     * Provides the number of files scanned by the Picker
     *
     * @return int The number of files
     */
    public function getFileScanCount(): int
    {
        return $this->_package['files'];
    }

    /**
     * Provide the full detailed accounting for each analyzed file
     *
     * @return array<mixed> The full detailed accounting.
     */
    public function getDetails(): array
    {
        return $this->_details;
    }

    /**
     * Recorded the detailed analysis of a file with a text/css MIME type
     *
     * @param array<mixed> $file The detailed analysis.
     */
    public function addCssFile(array $file): void
    {
        $this->_details[$file['mime_type']][$file['scan_path']] = [
            //'stats'     => $file['file_stats'],
            'selectors' => $file['selectors_by_line'],
            //'imports'   => $file['imports_by_line'],
            //'fonts'     => $file['fonts_by_line'],
            'syntax' => $file['syntax'],
            'semantics' => $file['semantics'],
        ];
    }
}
