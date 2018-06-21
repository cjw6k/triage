<?php

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

require_once __DIR__ . '/fixtures/bootstrap.php';
require_once VENDOR_ROOT . 'phpunit/phpunit/src/Framework/Assert/Functions.php';

class CommandContext implements Context, SnippetAcceptingContext
{

	private $_command_output;
	
	private $_command_exit_status;

	/**
	 * @Given My current working directory is the package root directory
	 */
	public function myCurrentWorkingDirectoryIsThePackageRootDirectory()
	{
		chdir(PACKAGE_ROOT);
		assertEquals(realpath(PACKAGE_ROOT), getcwd());
	}

	/**
	 * @Given I run the command :command
	 */
	public function iRunTheCommand($command)
	{
		exec($command, $this->_command_output, $this->_command_exit_status);
	}

	/**
	 * @Then I should see :text
	 */
	public function iShouldSee($text)
	{
		assertNotEmpty($this->_command_output);
		assertTrue(false !== strpos(implode(PHP_EOL, $this->_command_output), $text));
	}

	/**
	 * @Then I should not see :text
	 */
	public function iShouldNotSee($text)
	{
		assertNotEmpty($this->_command_output);
		assertTrue(false === strpos(implode(PHP_EOL, $this->_command_output), $text));
	}

	/**
	 * @Then the exit status should be :exit_status
	 */
	public function theExitStatusShouldBe($exit_status)
	{
		assertTrue($exit_status == $this->_command_exit_status);
	}

	/**
	 * @Given The directory :directory does not exist
	 */
	public function theDirectoryDoesNotExist($directory)
	{
		assertFalse(realpath($directory));
	}

	/**
	 * @Given The directory :directory exists
	 */
	public function theDirectoryExists($directory)
	{
		if(false === is_dir($directory)){
			assertTrue(mkdir($directory, 0755, true));
		}
		assertTrue(is_dir($directory));
	}

	/**
	 * @Given The directory :directory is not readable
	 */
	public function theDirectoryIsNotReadable($directory)
	{
		$this->theDirectoryExists($directory);
		if(is_readable($directory)){
			assertTrue(chmod($directory, 0200));
		}
		assertFalse(is_readable($directory));
	}	

}

