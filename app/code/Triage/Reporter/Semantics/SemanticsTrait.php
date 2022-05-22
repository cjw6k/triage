<?php

/**
 * The SemanticsTrait trait is herein defined.
 *
 * @link https://triage.cjwillcock.ca/
 */

declare(strict_types=1);

namespace Triage\Triage\Reporter\Semantics;

use function count;

trait SemanticsTrait
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
            foreach ($this->_troubles as $troubles) {
                $trouble_count += count($troubles);
            }
        }

        return $trouble_count;
    }
}
