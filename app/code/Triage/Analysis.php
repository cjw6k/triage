<?php
/**
 * The Analysis class is herein defined.
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
 * The Analysis class is a structured account of the syntax accuracy,
 * and semantic compatibility of the files in SOURCE.
 */
class Analysis
{

	/**
	 * The account of analysis done on SOURCE as a whole
	 *
	 * @var mixed[]
	 */
	private $_package = array(
		'directories' => array(),
		'files' => 0,
		'notices' => 0,
		'errors' => 0,
		'warnings' => 0,
		'css' => array(),
		'js' => array(),
		'html' => array(),
		'php' => array(),
	);

	/**
	 * The detailed account of analysis done on each file in SOURCE
	 *
	 * @var mixed[]
	 */
	private $_details = array();

	/**
	 * Capture the status of files and directories considered by the Picker
	 *
	 * @param mixed[] $status The count of directories and files analyzed.
	 *
	 * @return void
	 */
	public function setPickerStatus(array $status)
	{
		$this->_package['directories'] = $status['directories'];
		$this->_package['files'] = $status['files'];
	}

	/**
	 * Recorded the detailed analysis of a file with a generic MIME type
	 *
	 * @param mixed[] $file The detailed analysis.
	 *
	 * @return void
	 */
	public function addUnsupportedFile(array $file)
	{
		$this->_details[$file['mime_type']][$file['scan_path']] = $file['filename'];
	}

	/**
	 * Provides the number of directories scanned by the Picker
	 *
	 * @return integer The number of directories
	 */
	public function getDirectoryScanCount() : int
	{
		return $this->_package['directories'];
	}

	/**
	 * Provides the number of files scanned by the Picker
	 *
	 * @return integer The number of files
	 */
	public function getFileScanCount() : int
	{
		return $this->_package['files'];
	}

	/**
	 * Provide the full detailed accounting for each analyzed file
	 *
	 * @return mixed[] The full detailed accounting.
	 */
	public function getDetails() : array
	{
		return $this->_details;
	}

	/**
	 * Recorded the detailed analysis of a file with a text/css MIME type
	 *
	 * @param mixed[] $file The detailed analysis.
	 *
	 * @return void
	 */
	public function addCssFile(array $file)
	{
		$this->_details[$file['mime_type']][$file['scan_path']] = array(
			//'stats'     => $file['file_stats'],
			'selectors' => $file['selectors_by_line'],
			//'imports'   => $file['imports_by_line'],
			//'fonts'     => $file['fonts_by_line'],
			'syntax'    => $file['syntax'],
			'semantics' => $file['semantics'],
		);
	}

}
