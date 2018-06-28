<?php

declare(strict_types = 1);

namespace Triage\Triage\Reporter\Syntax;

trait SyntaxTrait
{

	private $_troubles = array();

	private $_count = null;

	public function __construct($troubles)
	{
		$this->_troubles = $troubles;
	}

	public function getCount()
	{
		if(null !== $this->_count){
			return $this->_count;
		}

		$trouble_count = 0;
		if(!empty($this->_troubles)){
			foreach($this->_troubles as $trouble_groups){
				foreach($trouble_groups as $troubles){
					$trouble_count += count($troubles);
				}
			}
		}

		return $trouble_count;
	}

	public function report($trouble_type)
	{
		foreach($this->_troubles[$trouble_type] as $line_number => $troubles){
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

}