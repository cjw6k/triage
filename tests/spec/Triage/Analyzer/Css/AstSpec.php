<?php

namespace spec\Triage\Triage\Analyzer\Css;

use Triage\Triage\Analyzer\Css\Ast;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AstSpec extends ObjectBehavior
{
	function let()
	{
		$file = array();
		$posh = new \stdClass();
		$microformats = new \stdClass();
		$this->beConstructedWith($file, $posh, $microformats);
	}
	
    function it_is_initializable()
    {
        $this->shouldHaveType(Ast::class);
    }
}
