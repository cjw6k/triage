<?php

declare(strict_types=1);

namespace spec\Triage\Triage\Reporter\Semantics;

use PhpSpec\ObjectBehavior;
use Triage\Triage\Reporter\Semantics\Posh;

class PoshSpec extends ObjectBehavior
{
    function let(): void
    {
        $trouble = [];
        $this->beConstructedWith($trouble);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(Posh::class);
    }
}
