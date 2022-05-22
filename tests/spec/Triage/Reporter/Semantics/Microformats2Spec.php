<?php

declare(strict_types=1);

namespace spec\Triage\Triage\Reporter\Semantics;

use PhpSpec\ObjectBehavior;
use Triage\Triage\Reporter\Semantics\Microformats2;

class Microformats2Spec extends ObjectBehavior
{
    function let(): void
    {
        $trouble = [];
        $this->beConstructedWith($trouble);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(Microformats2::class);
    }
}
