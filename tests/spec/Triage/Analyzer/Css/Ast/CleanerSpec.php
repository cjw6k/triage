<?php

namespace spec\Triage\Triage\Analyzer\Css\Ast;

use Triage\Triage\Analyzer\Css\Ast\Cleaner;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CleanerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Cleaner::class);
    }
}
