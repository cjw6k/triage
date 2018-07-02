<?php

namespace spec\Triage\Triage\Reporter\Semantics;

use Triage\Triage\Reporter\Semantics\Microformats2;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class Microformats2Spec extends ObjectBehavior
{
	function let()
	{
		$trouble = array();
		$this->beConstructedWith($trouble);
	}
	
    function it_is_initializable()
    {
        $this->shouldHaveType(Microformats2::class);
    }
}
