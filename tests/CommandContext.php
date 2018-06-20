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
	 * @Then the exit status should be :exit_status
	 */
	public function theExitStatusShouldBe($exit_status)
	{
		assertTrue($exit_status == $this->_command_exit_status);
	}

}

