<?php

/**
 * The SyntaxTrait trait is herein defined.
 *
 * @link https://triage.cjwillcock.ca/
 */

declare(strict_types=1);

namespace Triage\Triage\Reporter\Syntax;

use function count;
use function str_repeat;
use function strlen;

use const PHP_EOL;

trait SyntaxTrait
{
    /**
     * The set of troubles for this group
     *
     * @var array<mixed>
     */
    private array $_troubles = [];

    /**
     * The count of troubles in this group
     */
    private ?int $_count = null;

    /**
     * Capture the set of troubles for this group
     *
     * @param array<mixed> $troubles The set of troubles.
     */
    public function __construct(array $troubles)
    {
        $this->_troubles = $troubles;
    }

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
        if ($this->_count !== null) {
            return $this->_count;
        }

        $trouble_count = 0;

        if (! empty($this->_troubles)) {
            foreach ($this->_troubles as $trouble_groups) {
                foreach ($trouble_groups as $troubles) {
                    $trouble_count += count($troubles);
                }
            }
        }

        return $trouble_count;
    }

    /**
     * Output a message for each item in the set of troubles
     *
     * @param string $trouble_type The trouble type; an index into the troubles array.
     */
    public function report(string $trouble_type): void
    {
        foreach ($this->_troubles[$trouble_type] as $line_number => $troubles) {
            $prefix = "      - @ line $line_number: ";
            $prefix_length = strlen($prefix);

            echo $prefix;

            foreach ($troubles as $key => $trouble) {
                if (0 < $key) {
                    echo str_repeat(" ", $prefix_length);
                }

                // This would be nice with colour to indicate the matched portion within the selector
                echo "{$trouble['before']}", PHP_EOL;
                //echo "{$trouble['before']}\ts/{$trouble['match']}/{$trouble['after']}/", PHP_EOL;
            }
        }

        echo PHP_EOL;
    }
}
