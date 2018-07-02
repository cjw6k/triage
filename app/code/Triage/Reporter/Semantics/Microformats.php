<?php
/**
 * The Microformats class is herein defined.
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

namespace Triage\Triage\Reporter\Semantics;

/**
 * Microformats outputs messages about usage of microformats tokens found in analysis of CSS files.
 */
class Microformats
{

	use SemanticsTrait;

	/**
	 * Output a message for each item in the set of notices
	 *
	 * @return void
	 */
	public function report()
	{
		echo "   - Microformats: ", $this->getCount(), PHP_EOL;

		if(0 == $this->getCount()){
			return;
		}

		echo PHP_EOL, "      The website should not use microformats class names for styling", PHP_EOL, PHP_EOL;
		foreach($this->_troubles as $token => $lines){
			echo "      - .$token ";
			foreach($lines as $key => $line_number){
				if(0 != $key){
					echo "          ", str_repeat(" ", strlen($token));
				}
				echo "@ line $line_number", PHP_EOL;
			}
		}
		echo PHP_EOL;

	}

}
