<?php
/**
 * The Triage class is herein defined.
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

namespace Triage;

/**
 * The Triage class is the root of the application
 */
class Triage
{

	/**
	 * The current version number
	 *
	 * @var string
	 */
	private $_version = null;

	/**
	 * Command line arguments
	 *
	 * @var mixed[]
	 */
	private $_argv = null;

	/**
	 * Command line argument validity
	 *
	 * @var boolean
	 */
	private $_arguments_error = false;

	/**
	 * Relevant error message for invalid command line arguments
	 *
	 * @var string
	 */
	private $_arguments_error_message = '';

	/**
	 * Command line argument --help
	 *
	 * @var boolean
	 */
	private $_show_help = false;
	
	/**
	 * Command line argument --moo
	 *
	 * @var boolean
	 */
	private $_show_cows = false;

	/**
	 * Command line argument --version
	 *
	 * @var boolean
	 */
	private $_show_version = false;	
	
	/**
	 * Command line argument -p, --show-progress
	 *
	 * @var boolean
	 */
	private $_show_progress = false;
	
	/**
	 * Command line argument --all
	 *
	 * @var boolean
	 */
	private $_show_all_files = false;

	/**
	 * Command line argument, source to analyze
	 *
	 * @var string
	 */
	private $_source = null;

	/**
	 * A message displayed when the user needs usage information
	 *
	 * @var string
	 */
	private $_usage_message = "Usage: triage [OPTION]... SOURCE";

	/**
	 * A reference to the analyzer component
	 *
	 * @var Triage\Analyzer
	 */
	private $_analyzer = null;

	/**
	 * A reference to the reporter component
	 *
	 * @var Triage\Reporter
	 */
	private $_reporter = null;

	/**
	 * Capture version and command line arguments
	 *
	 * @param string       $version Current program version.
	 * @param mixed[]|null $argv    Command line arguments.
	 */
	public function __construct(string $version, $argv = null)
	{
		$this->_version = $version;
		$this->_argv = $argv;
	}

	/**
	 * Do all the steps to complete the triage run
	 *
	 * @return integer The exit status.
	 */
	public function run() : int
	{
		if(!$this->_hasRequiredArguments()){
			$this->_showUsage();
			return 1;
		}

		if($this->_show_help){
			$this->_showHelp();
			return 0;
		}

		if($this->_show_version){
			$this->_showVersion();
			return 0;
		}

		if(!$this->_hasValidSource()){
			$this->_showError();
			return 1;
		}

		$this->_initialize();

		$analysis = $this->_analyzer->analyze($this->_source);

		$this->_reporter->report($analysis);

		return 0;
	}

	/**
	 * Ensure the required command line arguments are present
	 *
	 * @return boolean true  Arguments are present.
	 *                 false Arguments are not present.
	 */
	private function _hasRequiredArguments() : bool
	{
		if(!is_array($this->_argv)){
			return false;
		}

		if(count($this->_argv) < 2){
			return false;
		}

		// The first item in the array is the executable itself, e.g. 'bin/triage'
		array_shift($this->_argv);

		while(!empty($this->_argv)){
			$argument = array_shift($this->_argv);

			$this->_parseArgument($argument);
		}

		return !$this->_arguments_error;
	}

	/**
	 * Parse and store valid command line arguments
	 *
	 * @param string $argument Command line argument to parse.
	 *
	 * @return void
	 */
	private function _parseArgument(string $argument)
	{
		switch($argument){
			case '--help':
				$this->_show_help = true;
				break;

			case '--version':
				$this->_show_version = true;
				break;
				
			case '-p':
			case '--show-progress':
				$this->_show_progress = true;
				break;
				
			case '--moo':
				$this->_show_cows = true;
				break;	
				
			case '-a':
			case '--show-all-files':
				$this->_show_all_files = true;
				break;

			default:
				if(null !== $this->_source){
					// Two is too many (so is any amount which is more than one)
					$this->_arguments_error = true;
				}
				$this->_source = $argument;
		}
	}

	/**
	 * Show the usage message
	 *
	 * @return void
	 */
	private function _showUsage()
	{
		echo $this->_usage_message, PHP_EOL, "Try 'triage --help' for more information.", PHP_EOL;
	}

	/**
	 * Show the help message
	 *
	 * @return void
	 */
	private function _showHelp()
	{
		echo $this->_usage_message, PHP_EOL, PHP_EOL;
		echo "OPTIONS:", PHP_EOL;
		echo "\t-a, --show-all-files\tshow all files scanned no matter which MIME type", PHP_EOL;
		echo "\t-p, --show-progress\tshow scan and analysis status while running", PHP_EOL;
		echo "\t--help\t\t\tdisplay this help and exit", PHP_EOL;
		echo "\t--version\t\toutput version information and exit", PHP_EOL, PHP_EOL;
		echo "triage is a utility which checks webapp sources", PHP_EOL;
		echo "for compatibility with microformats semantics", PHP_EOL, PHP_EOL;
		echo "EXAMPLES:", PHP_EOL;
		echo "\ttriage style.css", PHP_EOL;
		echo "\ttriage -p /var/www/a6a", PHP_EOL;
	}

	/**
	 * Show the version message.
	 *
	 * @return void
	 */
	private function _showVersion()
	{
		echo "triage {$this->_version}", PHP_EOL;
		echo "Copyright (c) 2018 by the contributors", PHP_EOL, PHP_EOL;
		echo "Permission is hereby granted, free of charge, to any person obtaining a copy", PHP_EOL;
		echo "of this software and associated documentation files (the \"Software\"), to deal", PHP_EOL;
		echo "in the Software without restriction, including without limitation the rights", PHP_EOL;
		echo "to use, copy, modify, merge, publish, distribute, sublicense, and/or sell", PHP_EOL;
		echo "copies of the Software, and to permit persons to whom the Software is", PHP_EOL;
		echo "furnished to do so, subject to the following conditions:", PHP_EOL, PHP_EOL;
		echo "The above copyright notice and this permission notice shall be included in all", PHP_EOL;
		echo "copies or substantial portions of the Software.", PHP_EOL, PHP_EOL;
		echo "THE SOFTWARE IS PROVIDED \"AS IS\", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR", PHP_EOL;
		echo "IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,", PHP_EOL;
		echo "FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE", PHP_EOL;
		echo "AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER", PHP_EOL;
		echo "LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,", PHP_EOL;
		echo "OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE", PHP_EOL;
		echo "SOFTWARE.", PHP_EOL;
	}

	/**
	 * Ensure SOURCE argument is a readable file or directory
	 *
	 * Provides a relevant error message if SOURCE is not valid.
	 *
	 * @return boolean true  The SOURCE is readable.
	 *                 false The SOURCE is not readable.
	 */
	private function _hasValidSource()
	{
		$realpath = realpath($this->_source);
		if(false === $realpath){
			$this->_arguments_error_message = "triage: no such file or directory";
			return false;
		}

		if(!is_readable($this->_source)){
			$this->_arguments_error_message = "triage: cannot open '$this->_source' for reading: Permission denied";
			return false;
		}

		return true;
	}

	/**
	 * Show the relevant error message for invalid arguments
	 *
	 * @return void
	 */
	private function _showError()
	{
		if(empty($this->_arguments_error_message)){
			return;
		}

		echo $this->_arguments_error_message, PHP_EOL;
	}

	/**
	 * Initialize the required components
	 *
	 * @return void
	 */
	private function _initialize()
	{
		$monitor = $this->_show_progress ? new Triage\Monitor\Progress() : new Triage\Monitor();
		
		$this->_analyzer = new Triage\Analyzer(
			new Triage\Picker(),
			$monitor
		);

		$this->_reporter = new Triage\Reporter(
			$this->_show_all_files,
			$this->_show_cows
		);
	}

}
