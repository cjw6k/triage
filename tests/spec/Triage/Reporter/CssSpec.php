<?php

declare(strict_types=1);

namespace spec\Triage\Triage\Reporter;

use PhpSpec\ObjectBehavior;
use Triage\Triage\Reporter\Css;

class CssSpec extends ObjectBehavior
{
    function let(): void
    {
        $show_cows = false;
        $this->beConstructedWith($show_cows);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(Css::class);
    }
}
