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
use function strnatcasecmp;
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
     *
     * @var array<mixed>
     */
    private array $picks = [];

    /**
     * The number of characters that appear in the real path before the SOURCE directory
     */
    private int $scan_path_prefix_length = 0;

    /**
     * The number of directories scanned
     */
    private int $directories_scanned = 0;

    /**
     * The number of files scanned
     */
    private int $files_scanned = 0;

    /**
     * The MIME types corresponding to file extensions
     */
    private array|object|null $mime_types_by_extension = null;

    /**
     * Acquires the mime type information from local cache
     */
    public function __construct()
    {
        $this->mime_types_by_extension = json_decode(
            file_get_contents(PACKAGE_ROOT . '/var/mime_types_by_extension.json')
        );
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
            $this->scan_path_prefix_length = strlen($realpath);
            $this->scanDirectory($realpath . '/');

            uasort(
                $this->picks,
                static function ($fs_a, $fs_b): int {
                    if ($fs_a['path'] == $fs_b['path']) {
                        return strnatcasecmp($fs_a['filename'], $fs_b['filename']);
                    }

                    return strnatcasecmp($fs_a['path'], $fs_b['path']);
                }
            );

            return $this->scanStatistics();
        }

        $this->enqueueFile($source);

        return $this->scanStatistics();
    }

    /**
     * Scan a directory for files
     *
     * @param string $directory The directory to scan.
     */
    private function scanDirectory(string $directory): void
    {
        $this->directories_scanned++;

        foreach (glob($directory . "*") as $path_to_file) {
            //$file = basename($filename);
            // if('node_modules' == $file){
                // continue;
            // }
            // if('vendor' == $file){
                // continue;
            // }
            if (is_dir($path_to_file)) {
                $this->scanDirectory($path_to_file . '/');
                continue;
            }

            $this->enqueueFile($path_to_file);
        }
    }

    /**
     * Queue a file for later use
     *
     * @param string $path_to_file A path to a file.
     */
    private function enqueueFile(string $path_to_file): void
    {
        $this->files_scanned++;
        $extension = substr($path_to_file, strrpos($path_to_file, '.') + 1);
        $mime_type = $this->mime_types_by_extension->$extension ?? '';

        if (empty($mime_type)) {
            $mime_type = "text/plain";
            //$this->_unknownExtension($extension, $path_to_file);
        }

        $this->picks[] = [
            'path' => dirname($path_to_file),
            'scan_path' => substr($path_to_file, $this->scan_path_prefix_length),
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
        return ! empty($this->picks);
    }

    /**
     * Provides information about a single file
     *
     * @return array<mixed> The file information
     */
    public function nextPick(): array
    {
        return array_shift($this->picks);
    }

    /**
     * Provides the statistics for this scan
     *
     * @return array<mixed> The statistics.
     */
    private function scanStatistics(): array
    {
        return [
            'directories' => $this->directories_scanned,
            'files' => $this->files_scanned,
        ];
    }
}
