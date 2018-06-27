<?php

namespace spec\Triage\Triage;

use Triage\Triage\Monitor;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MonitorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Monitor::class);
    }
}
