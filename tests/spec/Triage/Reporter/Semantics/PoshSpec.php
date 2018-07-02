<?php

namespace spec\Triage\Triage\Reporter\Semantics;

use Triage\Triage\Reporter\Semantics\Posh;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PoshSpec extends ObjectBehavior
{
	function let()
	{
		$trouble = array();
		$this->beConstructedWith($trouble);
	}
	
    function it_is_initializable()
    {
        $this->shouldHaveType(Posh::class);
    }
}
