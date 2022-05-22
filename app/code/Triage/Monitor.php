<?php

/**
 * The Monitor class is herein defined.
 *
 * @link https://triage.cjwillcock.ca/
 */

declare(strict_types=1);

namespace Triage\Triage;

use function exec;
use function implode;
use function preg_match;
use function strlen;
use function strval;
use function trim;

/**
 * Monitor silently tracks the progress of the analysis
 */
class Monitor
{
    /**
     * The number of columns in the active terminal
     */
    protected int $_terminal_width = 80;

    /**
     * The number of files scanned by the picker
     */
    protected int $_total;

    /**
     * The number of files scanned analyzed
     */
    protected int $_complete = 0;

    /**
     * The name of the file currently being scanned
     */
    protected string $_active_file = '';

    /**
     * The number of columns to be used for a progress bar display
     */
    private int $_progress_bar_width = 72;

    /**
     * Determine the number of columns in the terminal
     */
    public function __construct()
    {
        $this->_getTerminalWidth();
    }

    /**
     * Determine the number of columns in the active terminal
     */
    private function _getTerminalWidth(): void
    {
        exec('tput cols', $output, $return_code);

        if ($return_code == 0) {
            $this->_terminal_width = (int)trim($output[0]);

            return;
        }

        exec('mode con', $output, $return_code);

        if ($return_code != 0) {
            return;
        }

        if (preg_match('/Columns:\w+([0-9]+)/', implode(' ', $output), $match) == 0) {
            return;
        }

        $this->_terminal_width = (int)trim($match[1]);
    }

    /**
     * Capture the number of files staged for analysis
     *
     * @param int $total The number of files.
     */
    public function setTotal(int $total): void
    {
        $this->_total = $total;
        $this->_progress_bar_width = $this->_terminal_width - 1 - strlen(strval($total));
    }

    /**
     * Mark one of total as complete and update monitor on screen
     *
     * @param string $active_file The name of the file currently being analyzed.
     */
    public function mark(string $active_file): void
    {
        $this->_active_file = $active_file;
        $this->_complete++;
        $this->_updateScreen();
    }

    /**
     * A placeholder function, running once each time a new file is marked as active.
     */
    protected function _updateScreen(): void
    {
    }
}
