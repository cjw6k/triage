<?php

/**
 * The Css class is herein defined.
 *
 * @link https://triage.cjwillcock.ca/
 */

declare(strict_types=1);

namespace Triage\Triage\Reporter;

use function count;
use function str_pad;
use function strval;
use function ucfirst;

use const PHP_EOL;

/**
 * Css outputs messages about the analysis of CSS files
 */
class Css
{
    /**
     * The analysis of the CSS file.
     *
     * @var array<mixed>
     */
    private array $_analysis = [];

    /**
     * Show cowspeak for display of error messages
     */
    private bool $_show_cows = false;

    /**
     * Combined statistics collected for all CSS files included in this analysis
     *
     * @var array<mixed>
     */
    private array $_all_file_totals = [
        'syntax' => [
            'notices' => 0,
            'warnings' => 0,
            'errors' => 0,
        ],
        'semantics' => [
            'posh' => 0,
            'microformats' => 0,
            'microformats2' => 0,
        ],
    ];

    /**
     * Capture the option to show cows
     *
     * @param bool $show_cows The option flag.
     */
    public function __construct(bool $show_cows)
    {
        $this->_show_cows = $show_cows;
    }

    /**
     * Capture analysis of a single CSS file and output a report
     *
     * @param array<mixed> $analysis The section of analysis.
     */
    public function report(array $analysis): void
    {
        $this->_analysis = $analysis;

        $this->_report();
    }

    /**
     * Output the detailed report of syntax and semantics troubles found in the file
     */
    private function _report(): void
    {
        //showMe($this->_analysis['semantics']);
        $selector_count = 0;

        foreach ($this->_analysis['selectors'] as $lines) {
            foreach ($lines as $selectors) {
                $selector_count += count($selectors);
            }
        }

        echo " - Selectors: $selector_count", PHP_EOL;

        echo " - Syntax:", PHP_EOL;

        foreach (['notices', 'warnings', 'errors'] as $trouble_type) {
            $syntax_class = "\\Triage\\Triage\\Reporter\\Syntax\\" . ucfirst($trouble_type);
            $trouble_reporter = new $syntax_class($this->_analysis['syntax'][$trouble_type]);
            $this->_all_file_totals['syntax'][$trouble_type] += $trouble_reporter->getCount();
            $trouble_reporter->report($this->_show_cows);
        }

        echo " - Semantics:", PHP_EOL;

        foreach (['posh', 'microformats', 'microformats2'] as $trouble_type) {
            $semantic_class = "\\Triage\\Triage\\Reporter\\Semantics\\" . ucfirst($trouble_type);
            $trouble_reporter = new $semantic_class($this->_analysis['semantics'][$trouble_type]);
            $this->_all_file_totals['semantics'][$trouble_type] += $trouble_reporter->getCount();
            $trouble_reporter->report();
        }

        echo PHP_EOL;
    }

    /**
     * Output a combined summary report of all CSS files analyzed
     */
    public function summary(): void
    {
        echo "+---------------------------+", PHP_EOL;
        echo "|   All CSS Files Summary   |", PHP_EOL;
        echo "+---------------------------+", PHP_EOL;
        echo "|  Syntax                   |", PHP_EOL;
        echo "|  - notices:  " . str_pad(strval($this->_all_file_totals['syntax']['notices']), 11, " ") . "  |", PHP_EOL;
        echo "|  - warnings:  " . str_pad(strval($this->_all_file_totals['syntax']['warnings']), 10, " ") . "  |", PHP_EOL;
        echo "|  - errors:  " . str_pad(strval($this->_all_file_totals['syntax']['errors']), 12, " ") . "  |", PHP_EOL;
        echo "|                           |", PHP_EOL;
        echo "|  Semantics                |", PHP_EOL;
        echo "|  - POSH:  " . str_pad(strval($this->_all_file_totals['semantics']['posh']), 14, " ") . "  |", PHP_EOL;
        echo "|  - Microformats:  " . str_pad(strval($this->_all_file_totals['semantics']['microformats']), 6, " ") . "  |", PHP_EOL;
        echo "|  - Microformats2:  " . str_pad(strval($this->_all_file_totals['semantics']['microformats2']), 5, " ") . "  |", PHP_EOL;
        echo "+---------------------------+", PHP_EOL;
    }
}
