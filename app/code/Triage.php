<?php

/**
 * The Triage class is herein defined.
 *
 * @link https://triage.cjwillcock.ca/
 */

declare(strict_types=1);

namespace Triage;

use function array_shift;
use function count;
use function is_array;
use function is_readable;
use function realpath;

use const PHP_EOL;

/**
 * The Triage class is the root of the application
 */
class Triage
{
    /**
     * The current version number
     */
    private ?string $_version = null;

    /**
     * Command line arguments
     *
     * @var array<mixed>
     */
    private ?array $_argv = null;

    /**
     * Command line argument validity
     */
    private bool $_arguments_error = false;

    /**
     * Relevant error message for invalid command line arguments
     */
    private string $_arguments_error_message = '';

    /**
     * Command line argument --help
     */
    private bool $_show_help = false;

    /**
     * Command line argument --moo
     */
    private bool $_show_cows = false;

    /**
     * Command line argument --version
     */
    private bool $_show_version = false;

    /**
     * Command line argument -p, --show-progress
     */
    private bool $_show_progress = false;

    /**
     * Command line argument --all
     */
    private bool $_show_all_files = false;

    /**
     * Command line argument, source to analyze
     */
    private ?string $_source = null;

    /**
     * A message displayed when the user needs usage information
     */
    private string $_usage_message = "Usage: triage [OPTION]... SOURCE";

    /**
     * A reference to the analyzer component
     */
    private ?Triage\Analyzer $_analyzer = null;

    /**
     * A reference to the reporter component
     */
    private ?Triage\Reporter $_reporter = null;

    /**
     * Capture version and command line arguments
     *
     * @param string $version Current program version.
     * @param array<mixed>|null $argv Command line arguments.
     */
    public function __construct(string $version, ?array $argv = null)
    {
        $this->_version = $version;
        $this->_argv = $argv;
    }

    /**
     * Do all the steps to complete the triage run
     *
     * @return int The exit status.
     */
    public function run(): int
    {
        if (! $this->_hasRequiredArguments()) {
            $this->_showUsage();

            return 1;
        }

        if ($this->_show_help) {
            $this->_showHelp();

            return 0;
        }

        if ($this->_show_version) {
            $this->_showVersion();

            return 0;
        }

        if (! $this->_hasValidSource()) {
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
     * @return bool true Arguments are present.
 * false Arguments are not present.
     */
    private function _hasRequiredArguments(): bool
    {
        if (! is_array($this->_argv)) {
            return false;
        }

        if (count($this->_argv) < 2) {
            return false;
        }

        // The first item in the array is the executable itself, e.g. 'bin/triage'
        array_shift($this->_argv);

        while (! empty($this->_argv)) {
            $argument = array_shift($this->_argv);

            $this->_parseArgument($argument);
        }

        return ! $this->_arguments_error;
    }

    /**
     * Parse and store valid command line arguments
     *
     * @param string $argument Command line argument to parse.
     */
    private function _parseArgument(string $argument): void
    {
        switch ($argument) {
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
                if ($this->_source !== null) {
                    // Two is too many (so is any amount which is more than one)
                    $this->_arguments_error = true;
                }

                $this->_source = $argument;
        }
    }

    /**
     * Show the usage message
     */
    private function _showUsage(): void
    {
        echo $this->_usage_message, PHP_EOL, "Try 'triage --help' for more information.", PHP_EOL;
    }

    /**
     * Show the help message
     */
    private function _showHelp(): void
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
     */
    private function _showVersion(): void
    {
        echo "triage {$this->_version}", PHP_EOL, file_get_contents(__DIR__ . '/../../LICENSE');
    }

    /**
     * Ensure SOURCE argument is a readable file or directory
     *
     * Provides a relevant error message if SOURCE is not valid.
     *
     * @return bool true The SOURCE is readable.
 * false The SOURCE is not readable.
     */
    private function _hasValidSource(): bool
    {
        $realpath = realpath($this->_source);

        if ($realpath === false) {
            $this->_arguments_error_message = "triage: no such file or directory";

            return false;
        }

        if (! is_readable($this->_source)) {
            $this->_arguments_error_message = "triage: cannot open '$this->_source' for reading: Permission denied";

            return false;
        }

        return true;
    }

    /**
     * Show the relevant error message for invalid arguments
     */
    private function _showError(): void
    {
        if (empty($this->_arguments_error_message)) {
            return;
        }

        echo $this->_arguments_error_message, PHP_EOL;
    }

    /**
     * Initialize the required components
     */
    private function _initialize(): void
    {
        $monitor = $this->_show_progress
            ? new Triage\Monitor\Progress()
            : new Triage\Monitor();

        $this->_analyzer = new Triage\Analyzer(
            new Triage\Picker(),
            $monitor
        );

        $this->_reporter = new Triage\Reporter($this->_show_all_files, $this->_show_cows);
    }
}
