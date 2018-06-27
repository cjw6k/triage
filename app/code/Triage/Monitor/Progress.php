<?php

namespace Triage\Triage\Monitor;

class Progress extends \Triage\Triage\Monitor
{

	protected function _updateScreen()
	{
		//$progress = $this->_complete / $this->_total;
		//$progress_bar_complete = round($this->_progress_bar_width * $progress);

		$progress_ending = " " . str_pad($this->_complete, strlen($this->_total), " ", STR_PAD_LEFT) . " / $this->_total";
		$progress_beginning = " Analyzing: ";
		$space_available = $this->_terminal_width - strlen($progress_beginning) - strlen($progress_ending);

		$progress_message = "$progress_beginning ... $progress_ending";

		if($space_available > 25){
			$progress_file = $this->_getActiveFileProgress($space_available);
			$space_available -= strlen($progress_file);
			$progress_message = $progress_beginning . $progress_file . str_repeat(" ", $space_available) . "$progress_ending";
		}

		echo $progress_message;

		if($this->_total != $this->_complete){
			echo "\r";
			return;
		}

		echo PHP_EOL;
	}

	private function _getActiveFileProgress($space_available)
	{
		if(strlen($this->_active_file) <= $space_available){
			return $this->_active_file;
		}

		$file = "..." . substr($this->_active_file, strlen($this->_active_file) - $space_available + 3);
		return $file;
	}

}
