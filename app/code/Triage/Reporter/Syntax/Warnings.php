<?php
/**
 * The Warnings class is herein defined.
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
 * Warnings outputs messages about the syntax warnings generated from analysis of CSS files.
 */
class Warnings
{

	use SyntaxTrait{
		report as traitReport;
	}

	/**
	 * Output a message for each item in the set of warnings
	 *
	 * @return void
	 */
	public function report()
	{
		echo "   - Warnings: ", $this->getCount(), PHP_EOL;

		if(0 == $this->getCount()){
			return;
		}

		foreach(array_keys($this->_troubles) as $warning_type){
			$this->_describe($warning_type);
			$this->traitReport($warning_type);
		}

	}

	/**
	 * Output a message describing the type of warning encountered
	 *
	 * @param string $warning_type The type of warning.
	 *
	 * @return void
	 */
	private function _describe(string $warning_type)
	{
		echo PHP_EOL;

		switch($warning_type){
			case 'pseudo-element-confusion':
				echo "      Pseudo-class syntax used for a pseudo-element.";
				break;

			case 'pseudo-class-confusion':
				echo "      Pseudo-element syntax used for a pseudo-class.";
				break;

			case 'empty-not':
				echo "      The :not() pseudo-class requires a comma-separated list of one or more selectors as its argument.";
				break;

			case 'bad-pseudo-class-position':
				echo "      The position argument must be of the form <An+B> | even | odd.";
				break;

			case 'bad-characters':
				echo "      Selectors may not include extended white-space characters, e.g.: <200b>, <200c>, <200d>.";
				break;

			case 'quote-all-the-things':
				echo "      Selectors in the argument of a negation pseudo-class should not be quoted like string literals.";
				break;
		}

		echo PHP_EOL, PHP_EOL;
	}

}
