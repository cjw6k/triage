<?php
/**
 * The Microformats2 class is herein defined.
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
 * Microformats2 outputs messages about usage of microformats2 tokens found in analysis of CSS files.
 */
class Microformats2
{

	use SemanticsTrait;

	/**
	 * Output a message for each item in the set of notices
	 *
	 * @return void
	 */
	public function report()
	{
		echo "   - Microformats2: ", $this->getCount(), PHP_EOL;

		if(0 == $this->getCount()){
			return;
		}

		foreach($this->_troubles as $trouble_type => $troubles){
			$this->_describe($trouble_type);
			if($trouble_type == 'prefix-format'){
				$this->_list($trouble_type);
				continue;
			}
			foreach($troubles as $token => $lines){
				echo "      - .$token ";
				foreach($lines as $key => $line_number){
					if(0 != $key){
						echo "          ", str_repeat(" ", strlen($token));
					}
					echo "@ line $line_number", PHP_EOL;
				}
			}
		}
		echo PHP_EOL;
	}

	/**
	 * Provide the count of microformats2 troubles within the group
	 *
	 * Caches the count the first time it is called and provides the cached
	 * value for the next calls.
	 *
	 * @return integer The count of troubles.
	 */
	public function getCount() : int
	{
		if(null !== $this->_count){
			return $this->_count;
		}

		$trouble_count = 0;
		if(!empty($this->_troubles)){
			foreach($this->_troubles as $trouble_subtype => $troubles){
				if('prefix-format' == $trouble_subtype){
					continue;
				}
				$trouble_count += count($troubles);
			}
		}

		return $trouble_count;
	}

	/**
	 * Output a list of messages describing single-letter and double-letter prefix usage found in the CSS file
	 *
	 * @param string $trouble_type The type of prefix.
	 *
	 * @return void
	 */
	private function _list(string $trouble_type)
	{
		foreach($this->_troubles[$trouble_type] as $trouble_subtype => $subtype){
			switch($trouble_subtype){
				case 'single-letter':
					echo "      - Single Letter Prefixes: ", PHP_EOL;
					break;

				case 'double-letter':
					echo "      - Double Letter Prefixes: ", PHP_EOL;
					break;
			}
			foreach($subtype as $initial_letters => $details){
				echo "        - $initial_letters-* (" . count($details) . " token(s))", PHP_EOL;
				foreach($details as $detail){
					echo "          - " . $detail['token'] . " @ line " . $detail['line_number'], PHP_EOL;
				}
			}
		}
		echo PHP_EOL;
	}

	/**
	 * Output a message describing the type of microformats2 trouble encountered
	 *
	 * @param string $trouble_type The type of trouble.
	 *
	 * @return void
	 */
	private function _describe(string $trouble_type)
	{
		echo PHP_EOL;

		switch($trouble_type){
			case 'well-known':
				echo "      The well-known microformats2 class names should not be used for styling";
				break;

			case 'well-known-format':
				echo "      The webapp should not use for styling, class names that intersect the microformats2 class name prefix scheme (h-*, p-*, e-*, u-*, df-*)";
				break;

			case 'prefix-format':
				echo "      An active area of research for the microformats community is to gauge the usage of single and double letter prefixes";
				break;
		}

		echo PHP_EOL, PHP_EOL;
	}

}
