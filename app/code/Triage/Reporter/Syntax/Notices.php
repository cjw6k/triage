<?php
/**
 * The Notices class is herein defined.
 *
 * @copyright (C) 2018 by the contributors
 *
 * LICENSE: See the /LICENSE.md file for details (MIT)
 *
 * @package	Triage
 * @author	Christopher James Willcock <cjwillcock@ieee.org>
 * @link	https://triage.cjwillcock.ca/
 */

declare(strict_types = 1);

namespace Triage\Triage\Reporter\Syntax;

/**
 * Notices outputs messages about the syntax notices generated from analysis of CSS files.
 */
class Notices
{

	use SyntaxTrait{
		report as traitReport;
	}

	/**
	 * Output a message for each item in the set of notices
	 *
	 * @return void
	 */
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

	/**
	 * Output a message describing the type of notice encountered
	 *
	 * @param string $notice_type The type of notice.
	 *
	 * @return void
	 */
	private function _describe(string $notice_type)
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
