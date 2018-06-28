<?php

declare(strict_types = 1);

namespace Triage\Triage\Reporter\Syntax;

class Warnings
{

	use SyntaxTrait{
		report as traitReport;
	}

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

	private function _describe($warning_type)
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
