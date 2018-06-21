<?php

namespace spec\Triage\Triage;

use Triage\Triage\Analysis;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AnalysisSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Analysis::class);
    }
}
