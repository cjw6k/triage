<?php

declare(strict_types=1);

namespace spec\Triage\Triage\Monitor;

use PhpSpec\ObjectBehavior;
use Triage\Triage\Monitor\Progress;

class ProgressSpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(Progress::class);
    }
}
