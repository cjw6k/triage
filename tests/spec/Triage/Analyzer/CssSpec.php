<?php

namespace spec\Triage\Triage\Analyzer;

use Triage\Triage\Analyzer\Css;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CssSpec extends ObjectBehavior
{
	function let()
	{
		$file = array(
			'mime_type' => '',
			'scan_path' => '',
			'stats'     => '',
			'selectors' => '',
			'imports'   => '',
			'fonts'     => '',
			'syntax'    => '',
			'semantics' => '',
		);
		$plain_old_simple_html = new \stdClass();
		$this->beConstructedWith($file, $plain_old_simple_html);
	}
	
    function it_is_initializable()
    {
        $this->shouldHaveType(Css::class);
    }
}
