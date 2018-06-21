<?php
/**
 * The Reporter class is herein defined.
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
 * The Reporter class outputs a report from an Analysis
 */
class Reporter
{

	/**
	 * Output a report from an Analysis
	 *
	 * @param Analysis $analysis The analysis of the SOURCE.
	 *
	 * @return void
	 */
	public function report(Analysis $analysis)
	{
		$directories = $analysis->getDirectoryScanCount();
		echo "$directories director", $this->_pluralize($directories, 'y', 'ies'), PHP_EOL;

		$files = $analysis->getFileScanCount();
		echo "$files file", $this->_pluralize($files), PHP_EOL;

		foreach($analysis->getDetails() as $mime_type => $files_of_type){
			$this->_showMimeTypeDetails($mime_type);
			$this->_showFileDetails($files_of_type);
		}
	}

	/**
	 * Provide singular or plural suffixes based on count
	 *
	 * @param integer $count    The quantity of the item.
	 * @param string  $singular The singular suffix.
	 * @param string  $plural   The plural suffix.
	 *
	 * @return string The appropriate suffix
	 */
	private function _pluralize(int $count, string $singular = '', string $plural = 's')
	{
		return (1 == $count) ? $singular : $plural;
	}

	/**
	 * Output a formatted string with the given MIME type
	 *
	 * @param string $mime_type The MIME type to display.
	 *
	 * @return void
	 */
	private function _showMimeTypeDetails(string $mime_type)
	{
		echo PHP_EOL, "= $mime_type =", PHP_EOL;
	}

	/**
	 * Output the result of analysis on a given file
	 *
	 * @param mixed[] $files_of_type The detailed analysis.
	 *
	 * @return void
	 */
	private function _showFileDetails(array $files_of_type)
	{
		foreach(array_keys($files_of_type) as $scan_path){
			echo " .$scan_path", PHP_EOL;
		}
	}

}
