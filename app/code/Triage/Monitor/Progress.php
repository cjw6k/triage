<?php

/**
 * The Progress class is herein defined.
 *
 * @link https://triage.cjwillcock.ca/
 */

declare(strict_types=1);

namespace Triage\Triage\Monitor;

use Triage\Triage\Monitor;

use function str_pad;
use function str_repeat;
use function strlen;
use function strval;
use function substr;

use const PHP_EOL;
use const STR_PAD_LEFT;

/**
 * Progress outputs the current progress through the analysis
 */
class Progress extends Monitor
{
    /**
     * Output information to the console, about the file currently being analyzed
     */
    protected function updateScreen(): void
    {
        //$progress = $this->_complete / $this->_total;
        //$progress_bar_complete = round($this->_progress_bar_width * $progress);

        $progress_ending
            = " "
            . str_pad(
                strval($this->complete),
                strlen(strval($this->total)),
                " ",
                STR_PAD_LEFT
            )
            . " / $this->total";

        $progress_beginning = " Analyzing: ";
        $space_available = $this->terminal_width - strlen($progress_beginning) - strlen($progress_ending);

        $progress_message = "$progress_beginning ... $progress_ending";

        if ($space_available > 25) {
            $progress_file = $this->getActiveFileProgress($space_available);
            $space_available -= strlen($progress_file);
            $progress_message = $progress_beginning . $progress_file
                . str_repeat(" ", $space_available) . "$progress_ending";
        }

        echo $progress_message;

        if ($this->total != $this->complete) {
            echo "\r";

            return;
        }

        echo PHP_EOL;
    }

    /**
     * Truncates the name of the file currently being analyzed to fit the terminal
     *
     * @param int $space_available The number of columns available to display the file name.
     *
     * @return string The filename.
     */
    private function getActiveFileProgress(int $space_available): string
    {
        if (strlen($this->active_file) <= $space_available) {
            return $this->active_file;
        }

        return "..." . substr($this->active_file, strlen($this->active_file) - $space_available + 3);
    }
}
