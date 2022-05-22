<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;

use function Phpunit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertNotEmpty;
use function PHPUnit\Framework\assertTrue;

require_once __DIR__ . '/fixtures/bootstrap.php';
require_once VENDOR_ROOT . 'phpunit/phpunit/src/Framework/Assert/Functions.php';

class CommandContext implements Context, SnippetAcceptingContext
{
    private $_command_output;

    private $_command_exit_status;

    /** @Given My current working directory is the package root directory */
    public function myCurrentWorkingDirectoryIsThePackageRootDirectory(): void
    {
        chdir(PACKAGE_ROOT);
        assertEquals(realpath(PACKAGE_ROOT), getcwd());
    }

    /** @Given I run the command :command */
    public function iRunTheCommand($command): void
    {
        exec($command, $this->_command_output, $this->_command_exit_status);
    }

    /** @Then I should see :text */
    public function iShouldSee($text): void
    {
        assertNotEmpty($this->_command_output);
        assertTrue(strpos(implode(PHP_EOL, $this->_command_output), $text) !== false);
    }

    /** @Then I should not see :text */
    public function iShouldNotSee($text): void
    {
        assertNotEmpty($this->_command_output);
        assertTrue(strpos(implode(PHP_EOL, $this->_command_output), $text) === false);
    }

    /** @Then the exit status should be :exit_status */
    public function theExitStatusShouldBe($exit_status): void
    {
        assertTrue($exit_status == $this->_command_exit_status);
    }

    /** @Given The directory :directory does not exist */
    public function theDirectoryDoesNotExist($directory): void
    {
        assertFalse(realpath($directory));
    }

    /** @Given The directory :directory exists */
    public function theDirectoryExists($directory): void
    {
        if (is_dir($directory) === false) {
            assertTrue(mkdir($directory, 0755, true));
        }

        assertTrue(is_dir($directory));
    }

    /** @Given The directory :directory is not readable */
    public function theDirectoryIsNotReadable($directory): void
    {
        $this->theDirectoryExists($directory);

        if (is_readable($directory)) {
            assertTrue(chmod($directory, 0200));
        }

        assertFalse(is_readable($directory));
    }

    /** @Given The file :filename exists with: */
    public function theFileExistsWith($filename, PyStringNode $content): void
    {
        $dir = dirname($filename);

        if (! is_dir($dir)) {
            assertTrue(mkdir($dir, 0755, true));
        }

        assertTrue(is_dir($dir));
        assertTrue(file_put_contents($filename, $content) !== false);
    }

    /** @When I run triage with argument :argument */
    public function iRunTriageWithArgument($argument): void
    {
        exec("bin/triage '$argument'", $this->_command_output, $this->_command_exit_status);
    }

    /** @When I run triage in show-all-files mode with argument :argument */
    public function iRunTriageInShowAllFilesModeWithArgument($argument): void
    {
        exec("bin/triage '$argument' --show-all-files", $this->_command_output, $this->_command_exit_status);
    }

}
