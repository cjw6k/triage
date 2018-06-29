<?php
/**
 * The Monitor class is herein defined.
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

namespace Triage\Triage;

/**
 * Monitor silently tracks the progress of the analysis
 */
class Monitor
{

	/**
	 * The number of columns in the active terminal
	 *
	 * @var integer
	 */
	protected $_terminal_width = 80;


	/**
	 * The number of columns to be used for a progress bar display
	 *
	 * @var integer
	 */
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

	/**
	 * The name of the file currently being scanned
	 *
	 * @var string
	 */
	protected $_active_file = '';

	/**
	 * Determine the number of columns in the terminal
	 */
	public function __construct()
	{
		$this->_getTerminalWidth();
	}

	/**
	 * Determine the number of columns in the active terminal
	 *
	 * @return void
	 */
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
		$this->_progress_bar_width = $this->_terminal_width - 1 - strlen(strval($total));
	}

	/**
	 * Mark one of total as complete and update monitor on screen
	 *
	 * @param string $active_file The name of the file currently being analyzed.
	 *
	 * @return void
	 */
	public function mark(string $active_file)
	{
		$this->_active_file = $active_file;
		$this->_complete++;
		$this->_updateScreen();
	}

	/**
	 * A placeholder function, running once each time a new file is marked as active.
	 *
	 * @return void
	 */
	protected function _updateScreen()
	{
	}

}
