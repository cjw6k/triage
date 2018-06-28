<?php

namespace spec\Triage\Triage\Reporter;

use Triage\Triage\Reporter\Css;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CssSpec extends ObjectBehavior
{
	function let()
	{
		$show_cows = false;
		$this->beConstructedWith($show_cows);
	}
	
    function it_is_initializable()
    {
        $this->shouldHaveType(Css::class);
    }
}
