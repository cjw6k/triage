<?php
/**
 * The Analyzer class is herein defined.
 *
 * @copyright (C) 2018 by the contributors
 *
 * LICENSE: See the /LICENSE.md file for details (MIT)
 *
 * @package	Triage
 * @author	Christopher James Willcock <cjwillcock@ieee.org>
 * @link	https://triage.cjwillcock.ca/
 */

declare(strict_types=1);

namespace Triage\Triage;

/**
 * The Analyzer class scans all files in SOURCE and makes an Analysis
 */
class Analyzer
{

	/**
	 * A reference to the picker component
	 *
	 * @var Picker
	 */
	private $_picker = null;

	/**
	 * A reference to the analysis component
	 *
	 * @var Analysis
	 */
	private $_analysis = null;

	/**
	 * Capture picker component reference
	 *
	 * @param Picker $picker Picker component.
	 */
	public function __construct(Picker $picker)
	{
		$this->_picker = $picker;
	}

	/**
	 * Make an Analysis of the source files
	 *
	 * @param string $source The SOURCE to analyze.
	 *
	 * @return Analysis The analysis of the SOURCE.
	 */
	public function analyze(string $source) : Analysis
	{
		$this->_analysis = new Analysis();

		$status = $this->_picker->scan($source);

		$this->_analysis->setPickerStatus($status);

		while($this->_picker->hasPicks()){
			$this->_analyze($this->_picker->nextPick());
		}

		return $this->_analysis;
	}

	/**
	 * Perform analysis on a single file
	 *
	 * @param array $file The file to analyze.
	 *
	 * @return void;
	 */
	private function _analyze(array $file)
	{
		switch($file['mime_type']){
			default:
				$this->_analysis->addUnsupportedFile($file);
		}
	}

}
