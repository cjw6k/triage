<?php

/**
 * The Semantics trait is herein defined.
 *
 * @link https://triage.cjwillcock.ca/
 */

declare(strict_types=1);

namespace Triage\Triage\Reporter\Semantics;

use function count;

trait Semantics
{
    /**
     * The set of troubles for this group
     *
     * @var array<mixed>
     */
    private array $troubles = [];

    /**
     * The count of troubles in this group
     */
    private ?int $count = null;

    /**
     * Capture the set of troubles for this group
     *
     * @param array<mixed> $troubles The set of troubles.
     */
    public function __construct(array $troubles)
    {
        $this->troubles = $troubles;
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
        if ($this->count !== null) {
            return $this->count;
        }

        $trouble_count = 0;

        if (! empty($this->troubles)) {
            foreach ($this->troubles as $troubles) {
                $trouble_count += count($troubles);
            }
        }

        return $trouble_count;
    }
}
