<?php

namespace spec\Triage\Triage\Reporter\Syntax;

use Triage\Triage\Reporter\Syntax\Warnings;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class WarningsSpec extends ObjectBehavior
{
	function let()
	{
		$trouble = array();
		$this->beConstructedWith($trouble);
	}
	
    function it_is_initializable()
    {
        $this->shouldHaveType(Warnings::class);
    }
}
