<?php
/**
 * The Css class is herein defined.
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

namespace Triage\Triage\Reporter;

/**
 * Css outputs messages about the analysis of CSS files
 */
class Css
{

	/**
	 * The analysis of the CSS file.
	 *
	 * @var mixed[]
	 */
	private $_analysis = array();

	/**
	 * Show cowspeak for display of error messages
	 *
	 * @var boolean
	 */
	private $_show_cows = false;

	/**
	 * Combined statistics collected for all CSS files included in this analysis
	 *
	 * @var mixed[]
	 */
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

	/**
	 * Capture the option to show cows
	 *
	 * @param boolean $show_cows The option flag.
	 */
	public function __construct(bool $show_cows)
	{
		$this->_show_cows = $show_cows;
	}

	/**
	 * Capture analysis of a single CSS file and output a report
	 *
	 * @param mixed[] $analysis The section of analysis.
	 *
	 * @return void
	 */
	public function report(array $analysis)
	{
		$this->_analysis = $analysis;

		$this->_report();
	}

	/**
	 * Output the detailed report of syntax and semantics troubles found in the file
	 *
	 * @return void
	 */
	private function _report()
	{
		$selector_count = 0;
		foreach($this->_analysis['selectors'] as $lines){
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

	/**
	 * Output a listing of non-POSH HTML usage
	 *
	 * @return void
	 */
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

	/**
	 * Output a combined summary report of all CSS files analyzed
	 *
	 * @return void
	 */
	public function summary()
	{
		echo "+-------------------------+", PHP_EOL;
		echo "|  All CSS Files Summary  |", PHP_EOL;
		echo "+-------------------------+", PHP_EOL;
		echo "|  Syntax                 |", PHP_EOL;
		echo "|  - notices:  " . str_pad(strval($this->_all_file_totals['syntax']['notices']), 9, " ") . "  |", PHP_EOL;
		echo "|  - warnings:  " . str_pad(strval($this->_all_file_totals['syntax']['warnings']), 8, " ") . "  |", PHP_EOL;
		echo "|  - errors:  " . str_pad(strval($this->_all_file_totals['syntax']['errors']), 10, " ") . "  |", PHP_EOL;
		echo "|                         |", PHP_EOL;
		echo "|  Semantics              |", PHP_EOL;
		echo "|  - POSH:  " . str_pad(strval($this->_all_file_totals['semantics']['posh']), 12, " ") . "  |", PHP_EOL;
		echo "+-------------------------+", PHP_EOL;
	}

}
