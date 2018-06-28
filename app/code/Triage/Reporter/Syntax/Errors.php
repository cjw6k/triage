<?php

declare(strict_types = 1);

namespace Triage\Triage\Reporter\Syntax;

class Errors
{

	use SyntaxTrait{
		report as traitReport;
		getCount as traitGetCount;
	}

	public function getCount()
	{
		return $this->traitGetCount() / 3;
	}

	public function report($show_cows)
	{
		echo "   - Errors: ", $this->getCount(), PHP_EOL;

		if(0 == $this->getCount()){
			return;
		}

		echo PHP_EOL, "      Errors that can't be cleaned of unrecognized tokens are ignored.", PHP_EOL, PHP_EOL;
		foreach($this->_troubles as $line_number => $errors){
			$prefix = "      - @ line $line_number: ";
			$prefix_length = strlen($prefix);

			echo $prefix;

			foreach($errors as $key => $error){
				if(0 < $key){
					echo str_repeat(" ", $prefix_length);
				}
				echo "{$error['before']}\t(cleanup)=> {$error['after']}", PHP_EOL;

				if($show_cows){
					passthru("cowsay -W 72 " . escapeshellarg($error['exception']));
					echo PHP_EOL;

					continue;
				}

				echo $error['exception'], PHP_EOL;
			}
		}
		echo PHP_EOL;

	}

}
