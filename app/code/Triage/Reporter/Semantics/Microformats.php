<?php

/**
 * The Microformats class is herein defined.
 *
 * @link https://triage.cjwillcock.ca/
 */

declare(strict_types=1);

namespace Triage\Triage\Reporter\Semantics;

use function str_repeat;
use function strlen;

use const PHP_EOL;

/**
 * Microformats outputs messages about usage of microformats tokens found in analysis of CSS files.
 */
class Microformats
{
    use Semantics;

    /**
     * Output a message for each item in the set of notices
     */
    public function report(): void
    {
        echo "   - Microformats: ", $this->getCount(), PHP_EOL;

        if ($this->getCount() == 0) {
            return;
        }

        echo PHP_EOL, "      The website should not use microformats class names for styling", PHP_EOL, PHP_EOL;

        foreach ($this->troubles as $token => $lines) {
            echo "      - .$token ";

            foreach ($lines as $key => $line_number) {
                if ($key != 0) {
                    echo "          ", str_repeat(" ", strlen($token));
                }

                echo "@ line $line_number", PHP_EOL;
            }
        }

        echo PHP_EOL;
    }
}
