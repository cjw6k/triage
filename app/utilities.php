<?php

declare(strict_types=1);

/**
 * A debugging function which produces a var_dump of the argument, wrapped in <pre> for display on the web if not using the cli
 *
 * @param mixed $data the data to display
 *
 * @SuppressWarnings(PHPMD.DevelopmentCodeFragment)
 */
function showMe(mixed $data): void
{
    // Determine which function called this method
    $trace = debug_backtrace();

    // Determine appropriate newline character
    $newline = PHP_EOL;

    if (PHP_SAPI != 'cli') {
        $newline = '<br>';

        // If not in CLI, use HTML
        echo '<pre>';
    }

    echo 'DEBUG: ', $trace[0]['file'], ', Line ', $trace[0]['line'], $newline, $newline;
    var_dump($data);
    echo $newline;

    // If not in CLI, use HTML
    if (PHP_SAPI == 'cli') {
        return;
    }

    echo '</pre>';
}
