<?php

declare(strict_types=1);

namespace spec\Triage\Triage\Reporter\Syntax;

use PhpSpec\ObjectBehavior;
use Triage\Triage\Reporter\Syntax\Notices;

class NoticesSpec extends ObjectBehavior
{
    function let(): void
    {
        $trouble = [];
        $this->beConstructedWith($trouble);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(Notices::class);
    }
}
