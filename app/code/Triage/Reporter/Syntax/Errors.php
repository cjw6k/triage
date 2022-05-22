<?php

/**
 * The Errors class is herein defined.
 *
 * @link https://triage.cjwillcock.ca/
 */

declare(strict_types=1);

namespace Triage\Triage\Reporter\Syntax;

use function escapeshellarg;
use function passthru;
use function str_repeat;
use function strlen;

use const PHP_EOL;

/**
 * Errors outputs messages about the syntax errors generated from analysis of CSS files.
 */
class Errors
{
    use SyntaxTrait {
        report as traitReport;
        getCount as traitGetCount;
    }

    /**
     * Adjust the number of errors determined in the trait, to account for the shallow depth of the error reports
     *
     * @return int The number of errors in this group.
     */
    public function getCount(): int
    {
        return $this->traitGetCount() / 3;
    }

    /**
     * Output a message for each item in the set of errors
     *
     * @param bool $show_cows Display error messages via the cowsay program.
     */
    public function report(bool $show_cows): void
    {
        echo "   - Errors: ", $this->getCount(), PHP_EOL;

        if ($this->getCount() == 0) {
            return;
        }

        echo PHP_EOL, "      Errors that can't be cleaned of unrecognized tokens are ignored.", PHP_EOL, PHP_EOL;

        foreach ($this->_troubles as $line_number => $errors) {
            $prefix = "      - @ line $line_number: ";
            $prefix_length = strlen($prefix);

            echo $prefix;

            foreach ($errors as $key => $error) {
                if (0 < $key) {
                    echo str_repeat(" ", $prefix_length);
                }

                echo "{$error['before']}\t(cleanup)=> {$error['after']}", PHP_EOL;

                if ($show_cows) {
                    passthru("cowsay -W 72 " . escapeshellarg($error['exception']));
                    echo PHP_EOL;

                    continue;
                }

                echo $error['exception'], PHP_EOL;
            }
        }

        echo PHP_EOL;
    }
}
