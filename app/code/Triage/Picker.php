<?php
/**
 * The Picker class is herein defined.
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
 * The Picker class picks files recursively from SOURCE
 */
class Picker
{
	/**
	 * Scanned files that are queued for later use
	 *
	 * @var string[]
	 */
	private $_picks = array();

	/**
	 * The number of characters that appear in the real path before the SOURCE directory
	 *
	 * @var integer
	 */
	private $_scan_path_prefix_length = 0;

	/**
	 * The number of directories scanned
	 *
	 * @var integer
	 */
	private $_directories_scanned = 0;

	/**
	 * The number of files scanned
	 *
	 * @var integer
	 */
	private $_files_scanned = 0;

	/**
	 * The MIME types corresponding to file extensions
	 *
	 * @var mixed[]
	 */
	private $_mime_types_by_extension = null;

	/**
	 * Acquires the mime type information from local cache
	 */
	public function __construct()
	{
		$this->_mime_types_by_extension = json_decode(file_get_contents(PACKAGE_ROOT . '/var/mime_types_by_extension.json'));
	}

	/**
	 * Scan the SOURCE for files
	 *
	 * @param string $source The SOURCE path.
	 *
	 * @return mixed[]
	 */
	public function scan(string $source) : array
	{
		$realpath = realpath($source);
		if(is_dir($realpath)){
			$this->_scan_path_prefix_length = strlen($realpath);
			$this->_scanDirectory($realpath . '/');

			uasort(
				$this->_picks,
				function($fs_a, $fs_b){
					if($fs_a['path'] == $fs_b['path']){
						return $fs_a['filename'] > $fs_b['filename'];
					}
					return $fs_a['path'] > $fs_b['path'];
				}
			);

			return $this->_scanStatistics();
		}

		$this->_enqueueFile($source);

		return $this->_scanStatistics();
	}

	/**
	 * Scan a directory for files
	 *
	 * @param string $directory The directory to scan.
	 *
	 * @return void
	 */
	private function _scanDirectory(string $directory)
	{
		$this->_directories_scanned++;
		foreach(glob($directory . "*") as $path_to_file){
			//$file = basename($filename);
			// if('node_modules' == $file){
				// continue;
			// }
			// if('vendor' == $file){
				// continue;
			// }
			if(is_dir($path_to_file)){
				$this->_scanDirectory($path_to_file . '/');
				continue;
			}
			$this->_enqueueFile($path_to_file);
		}
	}

	/**
	 * Queue a file for later use
	 *
	 * @param string $path_to_file A path to a file.
	 *
	 * @return void
	 */
	private function _enqueueFile(string $path_to_file)
	{
		$this->_files_scanned++;
		$extension = substr($path_to_file, strrpos($path_to_file, '.') + 1);
		$mime_type = isset($this->_mime_types_by_extension->$extension) ? $this->_mime_types_by_extension->$extension : '';

		if(empty($mime_type)){
			$mime_type = "text/plain";
			//$this->_unknownExtension($extension, $path_to_file);
		}

		$this->_picks[] = array(
			'path' => dirname($path_to_file),
			'scan_path' => substr($path_to_file, $this->_scan_path_prefix_length),
			'filename' => basename($path_to_file),
			'mime_type' => $mime_type
		);
	}

	/**
	 * Check if there are more files awaiting analysis
	 *
	 * @return boolean true  There are more files to analyze.
	 *                 false There are no more files to analyze.
	 */
	public function hasPicks() : bool
	{
		return !empty($this->_picks);
	}

	/**
	 * Provides information about a single file
	 *
	 * @return mixed[] The file information
	 */
	public function nextPick() : array
	{
		return array_shift($this->_picks);
	}

	/**
	 * Provides the statistics for this scan
	 *
	 * @return mixed[] The statistics.
	 */
	private function _scanStatistics()
	{
		return array(
			'directories' => $this->_directories_scanned,
			'files' => $this->_files_scanned,
		);
	}

}
