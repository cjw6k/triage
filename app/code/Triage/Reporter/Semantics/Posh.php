<?php

/**
 * The Posh class is herein defined.
 *
 * @link https://triage.cjwillcock.ca/
 */

declare(strict_types=1);

namespace Triage\Triage\Reporter\Semantics;

use function count;
use function str_repeat;
use function strlen;

use const PHP_EOL;

/**
 * Posh outputs messages about the non-POSH usage found in analysis of CSS files.
 */
class Posh
{
    use SemanticsTrait;

    /**
     * Provide the count of troubles within the group
     *
     * Caches the count the first time it is called and provides the cached
     * value for the next calls.
     *
     * @return int The count of troubles.
     */
    public function getCount(): int
    {
        $posh_count = 0;

        foreach ($this->_troubles as $lines) {
            $posh_count += count($lines);
        }

        return $posh_count;
    }

    /**
     * Output a message for each item in the set of notices
     */
    public function report(): void
    {
        echo "   - POSH: ", $this->getCount(), PHP_EOL;

        if ($this->getCount() == 0) {
            return;
        }

        echo PHP_EOL, "      The website should use Plain Old Simple HTML(5)", PHP_EOL, PHP_EOL;

        foreach ($this->_troubles as $element => $lines) {
            echo "      - <$element> ";

            foreach ($lines as $key => $line_number) {
                if ($key != 0) {
                    echo "           ", str_repeat(" ", strlen($element));
                }

                echo "@ line $line_number", PHP_EOL;
            }
        }

        echo PHP_EOL;
    }
}
