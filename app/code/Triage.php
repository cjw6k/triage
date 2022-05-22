<?php

/**
 * The Triage class is herein defined.
 *
 * @link https://triage.cjwillcock.ca/
 */

declare(strict_types=1);

namespace Triage;

use Triage\Triage\Analyzer;
use Triage\Triage\Monitor;
use Triage\Triage\Monitor\Progress;
use Triage\Triage\Picker;
use Triage\Triage\Reporter;
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
    private ?string $version = null;

    /**
     * Command line arguments
     *
     * @var array<mixed>
     */
    private ?array $argv = null;

    /**
     * Command line argument validity
     */
    private bool $arguments_error = false;

    /**
     * Relevant error message for invalid command line arguments
     */
    private string $arguments_error_message = '';

    /**
     * Command line argument --help
     */
    private bool $show_help = false;

    /**
     * Command line argument --moo
     */
    private bool $show_cows = false;

    /**
     * Command line argument --version
     */
    private bool $show_version = false;

    /**
     * Command line argument -p, --show-progress
     */
    private bool $show_progress = false;

    /**
     * Command line argument --all
     */
    private bool $show_all_files = false;

    /**
     * Command line argument, source to analyze
     */
    private ?string $source = null;

    /**
     * A message displayed when the user needs usage information
     */
    private string $usage_message = "Usage: triage [OPTION]... SOURCE";

    /**
     * A reference to the analyzer component
     */
    private ?Analyzer $analyzer = null;

    /**
     * A reference to the reporter component
     */
    private ?Reporter $reporter = null;

    /**
     * Capture version and command line arguments
     *
     * @param string $version Current program version.
     * @param array<mixed>|null $argv Command line arguments.
     */
    public function __construct(string $version, ?array $argv = null)
    {
        $this->version = $version;
        $this->argv = $argv;
    }

    /**
     * Do all the steps to complete the triage run
     *
     * @return int The exit status.
     */
    public function run(): int
    {
        if (! $this->hasRequiredArguments()) {
            $this->showUsage();

            return 1;
        }

        if ($this->show_help) {
            $this->showHelp();

            return 0;
        }

        if ($this->show_version) {
            $this->showVersion();

            return 0;
        }

        if (! $this->hasValidSource()) {
            $this->showError();

            return 1;
        }

        $this->initialize();

        $analysis = $this->analyzer->analyze($this->source);

        $this->reporter->report($analysis);

        return 0;
    }

    /**
     * Ensure the required command line arguments are present
     *
     * @return bool true Arguments are present.
 * false Arguments are not present.
     */
    private function hasRequiredArguments(): bool
    {
        if (! is_array($this->argv)) {
            return false;
        }

        if (count($this->argv) < 2) {
            return false;
        }

        // The first item in the array is the executable itself, e.g. 'bin/triage'
        array_shift($this->argv);

        while (! empty($this->argv)) {
            $argument = array_shift($this->argv);

            $this->parseArgument($argument);
        }

        return ! $this->arguments_error;
    }

    /**
     * Parse and store valid command line arguments
     *
     * @param string $argument Command line argument to parse.
     */
    private function parseArgument(string $argument): void
    {
        switch ($argument) {
            case '--help':
                $this->show_help = true;

                break;

            case '--version':
                $this->show_version = true;

                break;

            case '-p':
            case '--show-progress':
                $this->show_progress = true;

                break;

            case '--moo':
                $this->show_cows = true;

                break;

            case '-a':
            case '--show-all-files':
                $this->show_all_files = true;

                break;

            default:
                if ($this->source !== null) {
                    // Two is too many (so is any amount which is more than one)
                    $this->arguments_error = true;
                }

                $this->source = $argument;
        }
    }

    /**
     * Show the usage message
     */
    private function showUsage(): void
    {
        echo $this->usage_message, PHP_EOL, "Try 'triage --help' for more information.", PHP_EOL;
    }

    /**
     * Show the help message
     */
    private function showHelp(): void
    {
        echo $this->usage_message, PHP_EOL, PHP_EOL;
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
    private function showVersion(): void
    {
        echo "triage {$this->version}", PHP_EOL, file_get_contents(__DIR__ . '/../../LICENSE');
    }

    /**
     * Ensure SOURCE argument is a readable file or directory
     *
     * Provides a relevant error message if SOURCE is not valid.
     *
     * @return bool true The SOURCE is readable.
 * false The SOURCE is not readable.
     */
    private function hasValidSource(): bool
    {
        $realpath = realpath($this->source);

        if ($realpath === false) {
            $this->arguments_error_message = "triage: no such file or directory";

            return false;
        }

        if (! is_readable($this->source)) {
            $this->arguments_error_message = "triage: cannot open '$this->source' for reading: Permission denied";

            return false;
        }

        return true;
    }

    /**
     * Show the relevant error message for invalid arguments
     */
    private function showError(): void
    {
        if (empty($this->arguments_error_message)) {
            return;
        }

        echo $this->arguments_error_message, PHP_EOL;
    }

    /**
     * Initialize the required components
     */
    private function initialize(): void
    {
        $monitor = $this->show_progress
            ? new Progress()
            : new Monitor();

        $this->analyzer = new Analyzer(
            new Picker(),
            $monitor
        );

        $this->reporter = new Reporter($this->show_all_files, $this->show_cows);
    }
}
