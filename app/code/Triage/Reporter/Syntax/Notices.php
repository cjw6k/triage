<?php

/**
 * The Notices class is herein defined.
 *
 * @link https://triage.cjwillcock.ca/
 */

declare(strict_types=1);

namespace Triage\Triage\Reporter\Syntax;

use function array_keys;

use const PHP_EOL;

/**
 * Notices outputs messages about the syntax notices generated from analysis of CSS files.
 */
class Notices
{
    use Syntax {
        report as traitReport;
    }

    /**
     * Output a message for each item in the set of notices
     */
    public function report(): void
    {
        echo "   - Notices: ", $this->getCount(), PHP_EOL;

        if ($this->getCount() == 0) {
            return;
        }

        foreach (array_keys($this->troubles) as $notice_type) {
            $this->describe($notice_type);
            $this->traitReport($notice_type);
        }
    }

    /**
     * Output a message describing the type of notice encountered
     *
     * @param string $notice_type The type of notice.
     */
    private function describe(string $notice_type): void
    {
        echo PHP_EOL
            . '      '
            . match ($notice_type) {
                'unrecognized-vendor-extension' => 'The vendor extensions activate new and experimental features in '
                    . 'browsers.',
                'experimental-pseudo-class' => 'There are non-standard, experimental pseudo-classes used here.',
                'experimental-pseudo-element' => 'There are non-standard, experimental pseudo-elements used here.',
                'ordinality' => 'A structural pseudo-class with a position argument of 0 will not match any elements '
                    . '(indexes start at 1).',
                default => "Unexpected notice type {$notice_type}."
            }
            . PHP_EOL
            . PHP_EOL;
    }
}
