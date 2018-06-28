<?php

declare(strict_types = 1);

namespace Triage\Triage\Reporter\Syntax;

class Notices
{

	use SyntaxTrait{
		report as traitReport;
	}

	public function report()
	{
		echo "   - Notices: ", $this->getCount(), PHP_EOL;

		if(0 == $this->getCount()){
			return;
		}

		foreach(array_keys($this->_troubles) as $notice_type){
			$this->_describe($notice_type);
			$this->traitReport($notice_type);
		}

	}

	private function _describe($notice_type)
	{
		echo PHP_EOL;

		switch($notice_type){
			case 'unrecognized-vendor-extension':
				echo "      The vendor extensions activate new and experimental features in browsers.";
				break;

			case 'experimental-pseudo-class':
				echo "      There are non-standard, experimental pseudo-classes used here.";
				break;

			case 'experimental-pseudo-element':
				echo "      There are non-standard, experimental pseudo-elements used here.";
				break;

			case 'ordinality':
				echo "      A structural pseudo-class with a position argument of 0 will not match any elements (indexes start at 1).";
				break;
		}

		echo PHP_EOL, PHP_EOL;
	}

}
