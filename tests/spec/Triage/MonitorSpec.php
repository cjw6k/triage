<?php

declare(strict_types=1);

namespace spec\Triage\Triage;

use PhpSpec\ObjectBehavior;
use Triage\Triage\Monitor;

class MonitorSpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(Monitor::class);
    }
}
