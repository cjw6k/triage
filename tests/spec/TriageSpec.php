<?php

namespace spec\Triage;

use Triage\Triage;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TriageSpec extends ObjectBehavior
{

	function let()
	{
		$this->beConstructedWith('dev-version');
		ob_start();
	}

	function letGo()
	{
		while(ob_get_level()){
			ob_end_clean();
		}
	}

    function it_is_initializable()
    {
        $this->shouldHaveType(Triage::class);
    }

	function it_exits_with_status_one_if_no_arguments_are_provided()
	{
		$this->beConstructedWith('dev-version', array());

		$this->run()->shouldReturn(1);
	}

	function it_exits_with_status_zero_if_help_argument_is_provided()
	{
		$this->beConstructedWith('dev-version', array('', '--help'));
		$this->run()->shouldReturn(0);
	}

}
