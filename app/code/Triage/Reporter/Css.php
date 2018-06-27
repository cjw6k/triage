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

		$notice_count = $this->getTroubleCount($this->_analysis['syntax']['notices']);
		$this->_all_file_totals['syntax']['notices'] += $notice_count;
		echo "   - Notices: ", $notice_count, PHP_EOL;
		if(0 < $notice_count){
			$this->_syntaxNotices();
		}

		$warning_count = $this->getTroubleCount($this->_analysis['syntax']['warnings']);
		$this->_all_file_totals['syntax']['warnings'] += $warning_count;
		echo "   - Warnings: ", $warning_count, PHP_EOL;
		if(0 < $warning_count){
			$this->_syntaxWarnings();
		}

		$error_count = $this->getTroubleCount($this->_analysis['syntax']['errors']);
		
		// Errors have a report which is not as deep as other troubles. Each error has 3 array entries.
		$error_count /= 3;
		
		$this->_all_file_totals['syntax']['errors'] += $error_count;		
		
		echo "   - Errors: ", $error_count, PHP_EOL;
		if(0 < $error_count){
			$this->_syntaxErrors();
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
			$this->_semanticsPosh();
		}

		echo PHP_EOL;
	}

	private function getTroubleCount($trouble_type)
	{
		$trouble_count = 0;
		if(!empty($trouble_type)){
			foreach($trouble_type as $trouble_groups){
				foreach($trouble_groups as $troubles){
					$trouble_count += count($troubles);	
				}
			}
		}

		return $trouble_count;		
	}
	
	private function _syntaxNotices()
	{
		foreach($this->_analysis['syntax']['notices'] as $notice_type => $notice_group){
			switch($notice_type){
				case 'unrecognized-vendor-extension':
					$this->_syntaxNoticesUnknownVendorExtension($notice_group);
				break;

				case 'experimental-pseudo-element':
					$this->_syntaxNoticesExperimentalPseudoElement($notice_group);
				break;

				case 'unrecognized-vendor-extension':
					$this->_syntaxNoticesExperimentalPseudoClass($notice_group);
				break;
			}
		}
	}

	private function _syntaxNoticesUnknownVendorExtension($notice_group)
	{
		echo PHP_EOL, "      The vendor extensions activate new and experimental features in browsers.", PHP_EOL, PHP_EOL;
		$this->_listSyntaxTroubles($notice_group);
	}

	private function _syntaxNoticesExperimentalPseudoClass($notice_group)
	{
		echo PHP_EOL, "      There are non-standard, experimental pseudo-classes used here.", PHP_EOL, PHP_EOL;
		$this->_listSyntaxTroubles($notice_group);
	}

	private function _syntaxNoticesExperimentalPseudoElement($notice_group)
	{
		echo PHP_EOL, "      The are non-standard, experimental pseudo-elements used here.", PHP_EOL, PHP_EOL;
		$this->_listSyntaxTroubles($notice_group);
	}

	private function _listSyntaxTroubles($trouble_group)
	{
		foreach($trouble_group as $line_number => $troubles){
			$prefix = "      - @ line $line_number: ";
			$prefix_length = strlen($prefix);

			echo $prefix;

			foreach($troubles as $key => $trouble){
				if(0 < $key){
					echo str_repeat(" ", $prefix_length);
				}
				// This would be nice with colour to indicate the matched portion within the selector
				echo "{$trouble['before']}", PHP_EOL;
				//echo "{$trouble['before']}\ts/{$trouble['match']}/{$trouble['after']}/", PHP_EOL;
			}
		}
		echo PHP_EOL;
	}

	private function _syntaxWarnings()
	{
		foreach($this->_analysis['syntax']['warnings'] as $warning_type => $warning_group){
			switch($warning_type){
				case 'pseudo-element-confusion':
					$this->_syntaxWarningsPseudoElementConfusion($warning_group);
					break;
				
				case 'pseudo-class-confusion':
					$this->_syntaxWarningsPseudoClassConfusion($warning_group);
					break;
				
				default:
					showMe($warning_type);
					exit(1);
			}
		}
	}

	private function _syntaxWarningsPseudoElementConfusion($warning_group)
	{
		echo PHP_EOL, "      Pseudo-class syntax used for a pseudo-element.", PHP_EOL, PHP_EOL;
		$this->_listSyntaxTroubles($warning_group);
	}	
	
	private function _syntaxWarningsPseudoClassConfusion($warning_group)
	{
		echo PHP_EOL, "      Pseudo-element syntax used for a pseudo-class.", PHP_EOL, PHP_EOL;
		$this->_listSyntaxTroubles($warning_group);
	}	
	
	private function _syntaxErrors()
	{
		echo PHP_EOL, "      Errors that can't be cleaned of unrecognized tokens are ignored.", PHP_EOL, PHP_EOL;
		foreach($this->_analysis['syntax']['errors'] as $line_number => $errors){
			$prefix = "      - @ line $line_number: ";
			$prefix_length = strlen($prefix);

			echo $prefix;

			foreach($errors as $key => $error){
				if(0 < $key){
					echo str_repeat(" ", $prefix_length);
				}
				echo "{$error['before']}\t(cleanup)=> {$error['after']}", PHP_EOL;
				
				if($this->_show_cows){
					passthru("cowsay -W 72 " . escapeshellarg($error['exception']));
					echo PHP_EOL;
					
					continue;
				}
				
				echo $error['exception'], PHP_EOL;
			}
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
