<?php

namespace spec\Triage\Triage;

use Triage\Triage\Reporter;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ReporterSpec extends ObjectBehavior
{
	function let()
	{
		$show_all_files = false;
		$show_cows = false;
		$this->beConstructedWith($show_all_files, $show_cows);
	}
	
    function it_is_initializable()
    {
        $this->shouldHaveType(Reporter::class);
    }
}
