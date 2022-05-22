<?php

/**
 * The Analyzer class is herein defined.
 *
 * @link https://triage.cjwillcock.ca/
 */

declare(strict_types=1);

namespace Triage\Triage;

use Triage\Triage\Analyzer\Css;

use function file_get_contents;
use function json_decode;

/**
 * Analyzer scans all files in SOURCE and makes an Analysis
 */
class Analyzer
{
    /**
     * A reference to the Picker component
     */
    private ?Picker $picker = null;

    /**
     * A reference to the Monitor component
     */
    private ?Monitor $monitor = null;

    /**
     * A reference to the analysis component
     */
    private ?Analysis $analysis = null;

    /**
     * The collection of tags that make up Plain Old Simple HTML (POSH)
     */
    private array|object $plain_old_simple_html = [];

    /**
     * The collection of well-known tokens that make up the microformats vocabularies
     */
    private array|object $microformats = [];

    /**
     * Capture picker component reference
     *
     * @param Picker $picker Picker component.
     * @param Monitor $monitor Monitor component.
     */
    public function __construct(Picker $picker, Monitor $monitor)
    {
        $this->picker = $picker;
        $this->monitor = $monitor;
        $this->plain_old_simple_html = json_decode(
            file_get_contents(PACKAGE_ROOT . '/var/plain_old_simple_html_elements.json')
        );
        $this->microformats = json_decode(
            file_get_contents(PACKAGE_ROOT . '/var/microformats_generation_class_tokens.json')
        );
    }

    /**
     * Make an Analysis of the source files
     *
     * @param string $source The SOURCE to analyze.
     *
     * @return Analysis The analysis of the SOURCE.
     */
    public function analyze(string $source): Analysis
    {
        $this->analysis = new Analysis();

        $status = $this->picker->scan($source);
        $this->monitor->setTotal($status['files']);

        $this->analysis->setPickerStatus($status);

        while ($this->picker->hasPicks()) {
            $file = $this->picker->nextPick();
            $this->analyzeFile($file);
            $this->monitor->mark($file['scan_path']);
        }

        return $this->analysis;
    }

    /**
     * Perform analysis on a single file
     *
     * @param array<array-key, string> $file The file to analyze.
     */
    private function analyzeFile(array $file): void
    {
        switch ($file['mime_type']) {
            case 'text/css':
                $this->analysis->addCssFile(
                    (new Css(
                        $file,
                        $this->plain_old_simple_html,
                        $this->microformats
                    ))->analyze()
                );

                break;

            default:
                $this->analysis->addUnsupportedFile($file);
        }
    }
}
