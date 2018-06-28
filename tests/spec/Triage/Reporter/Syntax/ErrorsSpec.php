<?php

namespace spec\Triage\Triage\Reporter\Syntax;

use Triage\Triage\Reporter\Syntax\Errors;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ErrorsSpec extends ObjectBehavior
{
	function let()
	{
		$trouble = array();
		$this->beConstructedWith($trouble);
	}
	
    function it_is_initializable()
    {
        $this->shouldHaveType(Errors::class);
    }
}
