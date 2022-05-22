<?php

declare(strict_types=1);

namespace spec\Triage\Triage\Reporter\Syntax;

use PhpSpec\ObjectBehavior;
use Triage\Triage\Reporter\Syntax\Errors;

class ErrorsSpec extends ObjectBehavior
{
    function let(): void
    {
        $trouble = [];
        $this->beConstructedWith($trouble);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(Errors::class);
    }
}
