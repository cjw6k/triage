<?php
/**
 * The Posh class is herein defined.
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
 * Posh outputs messages about the non-POSH usage found in analysis of CSS files.
 */
class Posh
{

	use SemanticsTrait;

	/**
	 * Provide the count of troubles within the group
	 *
	 * Caches the count the first time it is called and provides the cached
	 * value for the next calls.
	 *
	 * @return integer The count of troubles.
	 */
	public function getCount() : int
	{
		$posh_count = 0;
		foreach($this->_troubles as $lines){
			$posh_count += count($lines);
		}
		return $posh_count;
	}

	/**
	 * Output a message for each item in the set of notices
	 *
	 * @return void
	 */
	public function report()
	{
		echo "   - POSH: ", $this->getCount(), PHP_EOL;

		if(0 == $this->getCount()){
			return;
		}

		echo PHP_EOL, "      The website should use Plain Old Simple HTML(5)", PHP_EOL, PHP_EOL;

		foreach($this->_troubles as $element => $lines){
			echo "      - <$element> ";
			foreach($lines as $key => $line_number){
				if(0 != $key){
					echo "           ", str_repeat(" ", strlen($element));
				}
				echo "@ line $line_number", PHP_EOL;
			}
		}

		echo PHP_EOL;
	}

}
