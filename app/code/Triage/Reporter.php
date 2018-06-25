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
			switch($mime_type){
				case 'text/css':
					$this->_showCssFileAnalyses($files_of_type);
					break;

				default:
					$this->_showFileList($files_of_type);
			}
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
	 * Output the name and scan path of each file
	 *
	 * @param mixed[] $files_of_type The set of files.
	 *
	 * @return void
	 */
	private function _showFileList(array $files_of_type)
	{
		foreach(array_keys($files_of_type) as $scan_path){
			echo " $scan_path", PHP_EOL;
		}
	}

	/**
	 * Output the result of analysis on the CSS files
	 *
	 * @param mixed[] $files_of_type The detailed analyses.
	 *
	 * @return void
	 */
	private function _showCssFileAnalyses(array $files_of_type)
	{
		// later: new Reporter\Table, etc, etc
		foreach($files_of_type as $scan_path => $analysis){
			echo " $scan_path", PHP_EOL;

			$selector_count = 0;
			// foreach($analysis['selectors'] as $section => $lines){
			foreach($analysis['selectors'] as $lines){
				// foreach($lines as $line_number => $selectors){
				foreach($lines as $selectors){
					$selector_count += count($selectors);
				}
			}
			echo " - Selectors: $selector_count", PHP_EOL;

			echo " - Syntax:", PHP_EOL;
			echo "   - Notices: ", count($analysis['syntax']['notices']), PHP_EOL;
			echo "   - Warnings: ", count($analysis['syntax']['warnings']), PHP_EOL;
			echo "   - Errors: ", count($analysis['syntax']['errors']), PHP_EOL;

			echo " - Semantics:", PHP_EOL;

			$posh_count = 0;
			if(0 < $analysis['semantics']['posh']){
				foreach($analysis['semantics']['posh'] as $lines){
					$posh_count += count($lines);
				}
			}
			echo "   - POSH: ", $posh_count, PHP_EOL;

			echo PHP_EOL;
		}
	}

}
