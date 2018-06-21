<?php

namespace spec\Triage\Triage;

use Triage\Triage\Analyzer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AnalyzerSpec extends ObjectBehavior
{
	function let($picker)
	{
		$picker->beADoubleOf('\Triage\Triage\Picker');
		$this->beConstructedWith($picker);
	}
	
    function it_is_initializable()
    {
        $this->shouldHaveType(Analyzer::class);
    }
	
	function it_provides_an_analysis_after_analyzing()
	{
		$this->beConstructedWith(new \Triage\Triage\Picker());
		$this->analyze('tests/fixtures/analyzer-test/test.txt')->shouldHaveType(\Triage\Triage\Analysis::class);
	}
}
