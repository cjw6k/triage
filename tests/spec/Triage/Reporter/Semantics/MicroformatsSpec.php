<?php

namespace spec\Triage\Triage\Reporter\Semantics;

use Triage\Triage\Reporter\Semantics\Microformats;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MicroformatsSpec extends ObjectBehavior
{
	function let()
	{
		$trouble = array();
		$this->beConstructedWith($trouble);
	}
	
    function it_is_initializable()
    {
        $this->shouldHaveType(Microformats::class);
    }
}
