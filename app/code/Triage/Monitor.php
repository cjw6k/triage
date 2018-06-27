<?php

namespace Triage\Triage;

class Monitor
{

	protected $_terminal_width = 80;

	private $_progress_bar_width = 72;

	/**
	 * The number of files scanned by the picker
	 *
	 * @var integer
	 */
	protected $_total;

	/**
	 * The number of files scanned analyzed
	 *
	 * @var integer
	 */
	protected $_complete;

	protected $_active_file = '';

	public function __construct()
	{
		$this->_getTerminalWidth();
	}

	private function _getTerminalWidth()
	{
		exec('tput cols', $output, $return_code);
		if(0 == $return_code){
			$this->_terminal_width = trim($output[0]);
			return;
		}

		exec('mode con', $output, $return_code);
		if(0 == $return_code){
			if(0 == preg_match('/Columns:\w+([0-9]+)/', implode(' ', $output), $match)){
				return;
			}
			$this->_terminal_width = trim($match[1]);
		}
	}

	/**
	 * Capture the number of files staged for analysis
	 *
	 * @param integer $total The number of files.
	 *
	 * @return void
	 */
	public function setTotal(int $total)
	{
		$this->_total = $total;
		$this->_progress_bar_width = $this->_terminal_width - 1 - strlen($total);
	}

	/**
	 * Mark one of total as complete and update monitor on screen
	 *
	 * @return void
	 */
	public function mark($active_file)
	{
		$this->_active_file = $active_file;
		$this->_complete++;
		$this->_updateScreen();
	}

	protected function _updateScreen()
	{
	}

}
