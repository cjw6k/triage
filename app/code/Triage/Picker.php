<?php

/**
 * The Picker class is herein defined.
 *
 * @link https://triage.cjwillcock.ca/
 */

declare(strict_types=1);

namespace Triage\Triage;

use function array_shift;
use function basename;
use function dirname;
use function file_get_contents;
use function glob;
use function is_dir;
use function json_decode;
use function realpath;
use function strlen;
use function strrpos;
use function substr;
use function uasort;

/**
 * The Picker class picks files recursively from SOURCE
 */
class Picker
{
    /**
     * Scanned files that are queued for later use
     */
    private array $_picks = [];

    /**
     * The number of characters that appear in the real path before the SOURCE directory
     */
    private int $_scan_path_prefix_length = 0;

    /**
     * The number of directories scanned
     */
    private int $_directories_scanned = 0;

    /**
     * The number of files scanned
     */
    private int $_files_scanned = 0;

    /**
     * The MIME types corresponding to file extensions
     */
    private array|object|null $_mime_types_by_extension = null;

    /**
     * Acquires the mime type information from local cache
     */
    public function __construct()
    {
        $this->_mime_types_by_extension = json_decode(file_get_contents(PACKAGE_ROOT . '/var/mime_types_by_extension.json'));
    }

    /**
     * Scan the SOURCE for files
     *
     * @param string $source The SOURCE path.
     *
     * @return array<mixed>
     */
    public function scan(string $source): array
    {
        $realpath = realpath($source);

        if (is_dir($realpath)) {
            $this->_scan_path_prefix_length = strlen($realpath);
            $this->_scanDirectory($realpath . '/');

            uasort(
                $this->_picks,
                static function ($fs_a, $fs_b): int {
                    if ($fs_a['path'] == $fs_b['path']) {
                        return strnatcasecmp($fs_a['filename'], $fs_b['filename']);
                    }

                    return strnatcasecmp($fs_a['path'], $fs_b['path']);
                }
            );

            return $this->_scanStatistics();
        }

        $this->_enqueueFile($source);

        return $this->_scanStatistics();
    }

    /**
     * Scan a directory for files
     *
     * @param string $directory The directory to scan.
     */
    private function _scanDirectory(string $directory): void
    {
        $this->_directories_scanned++;

        foreach (glob($directory . "*") as $path_to_file) {
            //$file = basename($filename);
            // if('node_modules' == $file){
                // continue;
            // }
            // if('vendor' == $file){
                // continue;
            // }
            if (is_dir($path_to_file)) {
                $this->_scanDirectory($path_to_file . '/');
                continue;
            }

            $this->_enqueueFile($path_to_file);
        }
    }

    /**
     * Queue a file for later use
     *
     * @param string $path_to_file A path to a file.
     */
    private function _enqueueFile(string $path_to_file): void
    {
        $this->_files_scanned++;
        $extension = substr($path_to_file, strrpos($path_to_file, '.') + 1);
        $mime_type = $this->_mime_types_by_extension->$extension ?? '';

        if (empty($mime_type)) {
            $mime_type = "text/plain";
            //$this->_unknownExtension($extension, $path_to_file);
        }

        $this->_picks[] = [
            'path' => dirname($path_to_file),
            'scan_path' => substr($path_to_file, $this->_scan_path_prefix_length),
            'filename' => basename($path_to_file),
            'mime_type' => $mime_type,
        ];
    }

    /**
     * Check if there are more files awaiting analysis
     *
     * @return bool true There are more files to analyze.
 * false There are no more files to analyze.
     */
    public function hasPicks(): bool
    {
        return ! empty($this->_picks);
    }

    /**
     * Provides information about a single file
     *
     * @return array<mixed> The file information
     */
    public function nextPick(): array
    {
        return array_shift($this->_picks);
    }

    /**
     * Provides the statistics for this scan
     *
     * @return array<mixed> The statistics.
     */
    private function _scanStatistics(): array
    {
        return [
            'directories' => $this->_directories_scanned,
            'files' => $this->_files_scanned,
        ];
    }
}
