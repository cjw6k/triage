<?php

declare(strict_types=1);

namespace spec\Triage\Triage;

use PhpSpec\ObjectBehavior;
use Triage\Triage\Reporter;

class ReporterSpec extends ObjectBehavior
{
    function let(): void
    {
        $show_all_files = false;
        $show_cows = false;
        $this->beConstructedWith($show_all_files, $show_cows);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(Reporter::class);
    }
}
