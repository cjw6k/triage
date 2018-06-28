<?php

namespace spec\Triage\Triage\Reporter\Syntax;

use Triage\Triage\Reporter\Syntax\Notices;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class NoticesSpec extends ObjectBehavior
{
	function let()
	{
		$trouble = array();
		$this->beConstructedWith($trouble);
	}
	
    function it_is_initializable()
    {
        $this->shouldHaveType(Notices::class);
    }
}
