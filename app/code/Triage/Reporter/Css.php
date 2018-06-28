<?php

namespace Triage\Triage\Reporter;

class Css
{

	private $_analysis;

	private $_show_cows = false;

	private $_all_file_totals = array(
		'syntax' => array(
			'notices' => 0,
			'warnings' => 0,
			'errors' => 0,
		),
		'semantics' => array(
			'posh' => 0
		)
	);

	public function __construct($show_cows)
	{
		$this->_show_cows = $show_cows;
	}

	public function report($analysis)
	{
		$this->_analysis = $analysis;

		$this->_report();
	}

	private function _report()
	{
		$selector_count = 0;
		// foreach($analysis['selectors'] as $section => $lines){
		foreach($this->_analysis['selectors'] as $lines){
			// foreach($lines as $line_number => $selectors){
			foreach($lines as $selectors){
				$selector_count += count($selectors);
			}
		}
		echo " - Selectors: $selector_count", PHP_EOL;

		echo " - Syntax:", PHP_EOL;

		foreach(array('notices', 'warnings', 'errors') as $trouble_type){
			$syntax_class = "\\Triage\\Triage\\Reporter\\Syntax\\" . ucfirst($trouble_type);
			$trouble_reporter = new $syntax_class($this->_analysis['syntax'][$trouble_type]);
			$this->_all_file_totals['syntax'][$trouble_type] += $trouble_reporter->getCount();
			$trouble_reporter->report($this->_show_cows);
		}

		// $warning_count = $this->getTroubleCount($this->_analysis['syntax']['warnings']);
		// $this->_all_file_totals['syntax']['warnings'] += $warning_count;
		// echo "   - Warnings: ", $warning_count, PHP_EOL;
		// if(0 < $warning_count){
			// $this->_syntaxWarnings();
		// }

		// $error_count = $this->getTroubleCount($this->_analysis['syntax']['errors']);

		// // Errors have a report which is not as deep as other troubles. Each error has 3 array entries.
		// $error_count /= 3;

		// $this->_all_file_totals['syntax']['errors'] += $error_count;

		// echo "   - Errors: ", $error_count, PHP_EOL;
		// if(0 < $error_count){
			// //new Syntax\Errors($this->_analysis['syntax']['errors']);
			// $this->_syntaxErrors();
		// }

		echo " - Semantics:", PHP_EOL;

		$posh_count = 0;
		if(!empty($this->_analysis['semantics']['posh'])){
			foreach($this->_analysis['semantics']['posh'] as $lines){
				$posh_count += count($lines);
			}
		}

		$this->_all_file_totals['semantics']['posh'] += $posh_count;

		echo "   - POSH: ", $posh_count, PHP_EOL;
		if(0 < $posh_count){
			//new Semantics\Posh($this->_analysis['semantics']['posh']);
			$this->_semanticsPosh();
		}

		echo PHP_EOL;
	}

	private function _semanticsPosh()
	{
		echo PHP_EOL, "      The website should use Plain Old Simple HTML(5)", PHP_EOL, PHP_EOL;
		foreach($this->_analysis['semantics']['posh'] as $element => $lines){
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

	public function summary()
	{
		echo "+-------------------------+", PHP_EOL;
		echo "|  All CSS Files Summary  |", PHP_EOL;
		echo "+-------------------------+", PHP_EOL;
		echo "|  Syntax                 |", PHP_EOL;
		echo "|  - notices:  " . str_pad($this->_all_file_totals['syntax']['notices'], 9, " ") . "  |", PHP_EOL;
		echo "|  - warnings:  " . str_pad($this->_all_file_totals['syntax']['warnings'], 8, " ") . "  |", PHP_EOL;
		echo "|  - errors:  " . str_pad($this->_all_file_totals['syntax']['errors'], 10, " ") . "  |", PHP_EOL;
		echo "|                         |", PHP_EOL;
		echo "|  Semantics              |", PHP_EOL;
		echo "|  - POSH:  " . str_pad($this->_all_file_totals['semantics']['posh'], 12, " ") . "  |", PHP_EOL;
		echo "+-------------------------+", PHP_EOL;
	}

}
