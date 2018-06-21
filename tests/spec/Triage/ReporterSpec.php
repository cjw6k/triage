<?php

namespace spec\Triage\Triage;

use Triage\Triage\Reporter;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ReporterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Reporter::class);
    }
}
