<?php

namespace spec\Triage\Triage\Monitor;

use Triage\Triage\Monitor\Progress;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProgressSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Progress::class);
    }
}
