<?php

/**
 * The Cleaner class is herein defined.
 *
 * @link https://triage.cjwillcock.ca/
 */

declare(strict_types=1);

namespace Triage\Triage\Analyzer\Css\Ast;

use function preg_replace_callback_array;
use function trim;

/**
 * Cleaner attempts to correct syntax issues and \PhpCss limitations to fully scan a CSS file
 */
class Cleaner
{
    /**
     * The current CSS selector under examination
     */
    private string $selector = "";

    /**
     * An account of any changes made to the CSS selector to cause it to parse
     *
     * @var array<mixed>
     */
    private array $replacements = [];

    /**
     * Attempt to clean syntax problems with the selector
     *
     * @param string $selector The CSS selector that does not currently parse.
     *
     * @return int The number of replacements made in the selector.
     */
    public function clean(string $selector): int
    {
        $this->reset();

        $this->selector = preg_replace_callback_array(
            [
                // pseudo-class syntax used for a pseudo-element
                '/(?<!:)(:(?:after|before|placeholder|selection|first-letter|first-line|backdrop|cue|grammar-error'
                    . '|marker|slotted|spelling-error))/' => static fn ($match) => ":{$match[1]}",

                // pseudo-element syntax used for a pseudo-class
                '/::(visited|hover|link|focus|active|empty|checked|disabled|first-child|first-of-type|last-child'
                    . '|last-of-type|not\(.*\)|nth-child\(.*\)|required|nth-last-child\(.*\)|nth-last-of-type\(.*\)'
                    . '|nth-of-type\(.*\)|only-child|only-of-type|valid|invalid|indeterminate|any|any-link|default'
                    . '|defined|dir\(.*\)|enabled|first|fullscreen|host|host\(.*\)|host-context\(.*\)|in-range'
                    . '|lang\(.*\)|left|optional|out-of-range|read-only|read-write|right|root|scope|target)/'
                    => static fn ($match) => ":{$match[1]}",

                /*
                 * nth-*() parameter of 0, which is syntactically valid but useless and not recognized in \PhpCss
                 * (positions start at 1)
                 */
                '/:nth\-(child|of\-type|last\-child)\(0\)/'
                    => fn ($match): string => $this->nthChildZero($match, $selector),

                // invalid characters appearing in selectors
                //   <200b> zero width space
                //   <200c> zero width non-joiner
                //   <200d> zero width joiner
                '/[\x{200B}-\x{200D}]/u' => fn ($match): string => $this->badCharacters($match, $selector),

                // quoted a selector in the argument to a negation pseudo-class, as if it was a string literal
                "/:not\('(.+)'\)/" => fn ($match): string => $this->quotedNegationArgument($match, $selector),

                // used a class or Id instead of a pseudo-class position
                "/:nth\-((?:last\-)?(?:child|of\-type))\((?:\.|#).*\)/"
                    => fn ($match): string => $this->badPseudoClassPosition($match, $selector),

                // unrecognized vendor extension
                "/:?(?::-ms-|:-webkit-|:-moz-|:-o-)[0-9a-z\-]*+/"
                    => fn ($match): string => $this->vendorPrefix($match, $selector),

                // Empty :not()
                '/:not\(\)/' => fn ($match): string => $this->emptyNot($match, $selector),

                // Experimental pseudo-elements
                '/(.*)?::(?:placeholder|backdrop|marker|spelling-error|grammar-error)([^-0-9a-zA-Z]*+)?/'
                    => fn ($match): string => $this->experimentalPseudoElement($match, $selector),

                // Unsupported-in-PhpCss pseudo-elements
                '/(.*)?::(?:selection|cue|slotted)([^-0-9a-zA-Z]*+)?$/'
                    => static fn ($match) => "${match[1]}${match[2]}",

                // Experimental pseudo-classes
                '/(.*)?:(?:any-link|dir\(.*\)|fullscreen|host\(.*\)|host-context\(.*\))([^-0-9a-zA-Z]*+)?/'
                    => fn ($match): string => $this->experimentalPseudoClass($match, $selector),

                // Unsupported-in-PhpCss pseudo-classes
                '/(.*)?:(?:required|valid|default|defined|first|host|in-range|indeterminate|invalid|left|optional'
                    . '|out-of-range|read-only|read-write|right|scope)([^-0-9a-zA-Z]*+)?/'
                    => static fn ($match) => "${match[1]}${match[2]}",
            ],
            $selector,
            -1,
            $replacement_count
        );

        return $replacement_count;
    }

    /**
     * Reset the cleaner to clean a new selector
     */
    private function reset(): void
    {
        $this->replacements = [];
    }

    /**
     * Correct syntax where an nth-child of zero is the trouble
     *
     * @param array<string> $match The section of selector that has the trouble.
     * @param string $selector The selector before applying this replacement.
     *
     * @return string The replacement string for that section of the selector.
     */
    private function nthChildZero(array $match, string $selector): string
    {
        return $this->recordReplacement('notices/ordinality', $selector, $match[0], ":nth-{$match[1]}(0n)");
    }

    /**
     * Correct syntax where invalid characters are the trouble
     *
     * @param array<string> $match The section of selector that has the trouble.
     * @param string $selector The selector before applying this replacement.
     *
     * @return string The replacement string for that section of the selector.
     */
    private function badCharacters(array $match, string $selector): string
    {
        return $this->recordReplacement('warnings/bad-characters', $selector, $match[0], '');
    }

    /**
     * Correct syntax where a quoted argument to a negation pseudo-class is the trouble
     *
     * @param array<string> $match The section of selector that has the trouble.
     * @param string $selector The selector before applying this replacement.
     *
     * @return string The replacement string for that section of the selector.
     */
    private function quotedNegationArgument(array $match, string $selector): string
    {
        return $this->recordReplacement('warnings/quote-all-the-things', $selector, $match[0], ":not({$match[1]})");
    }

    /**
     * Correct syntax where an invalid pseudo-class position is the trouble
     *
     * @param array<string> $match The section of selector that has the trouble.
     * @param string $selector The selector before applying this replacement.
     *
     * @return string The replacement string for that section of the selector.
     */
    private function badPseudoClassPosition(array $match, string $selector): string
    {
        return $this->recordReplacement(
            'warnings/bad-pseudo-class-position',
            $selector,
            $match[0],
            ":nth-{$match[1]}(0n)"
        );
    }

    /**
     * Correct syntax where an unrecognized vendor extension is the trouble
     *
     * @param array<string> $match The section of selector that has the trouble.
     * @param string $selector The selector before applying this replacement.
     *
     * @return string The replacement string for that section of the selector.
     */
    private function vendorPrefix(array $match, string $selector): string
    {
        return $this->recordReplacement('notices/unrecognized-vendor-extension', $selector, $match[0], '');
    }

    /**
     * Correct syntax where an empty :not() is the trouble
     *
     * @param array<string> $match The section of selector that has the trouble.
     * @param string $selector The selector before applying this replacement.
     *
     * @return string The replacement string for that section of the selector.
     */
    private function emptyNot(array $match, string $selector): string
    {
        return $this->recordReplacement('warnings/empty-not', $selector, $match[0], '');
    }

    /**
     * Alter syntax to parse where an experimental pseudo-element is the trouble
     *
     * @param array<string> $match The section of selector that has the trouble.
     * @param string $selector The selector before applying this replacement.
     *
     * @return string The replacement string for that section of the selector.
     */
    private function experimentalPseudoElement(array $match, string $selector): string
    {
        return $this->recordReplacement(
            'notices/experimental-pseudo-element',
            $selector,
            $match[0],
            "${match[1]}${match[2]}"
        );
    }

    /**
     * Alter syntax to parse where an experimental pseudo-class is the trouble
     *
     * @param array<string> $match The section of selector that has the trouble.
     * @param string $selector The selector before applying this replacement.
     *
     * @return string The replacement string for that section of the selector.
     */
    private function experimentalPseudoClass(array $match, string $selector): string
    {
        return $this->recordReplacement(
            'notices/experimental-pseudo-class',
            $selector,
            $match[0],
            "${match[1]}${match[2]}"
        );
    }

    /**
     * Record the replacement of a section of a selector, for later reporting
     *
     * @param string $type The type of replacement.
     * @param string $selector The unmodified selector.
     * @param string $match The section of the selector that has the trouble.
     * @param string $after The new content to used for the matched section.
     *
     * @return string The replacement string for that section of the selector
     */
    private function recordReplacement(string $type, string $selector, string $match, string $after): string
    {
        $this->replacements[$type][] = [
            'before' => $selector,
            'match' => $match,
            'after' => $after,
        ];

        return $after;
    }

    /**
     * Provide the selector after any replacements
     *
     * @return string The selector.
     */
    public function getCleanedSelector(): string
    {
        return trim($this->selector);
    }

    /**
     * Provide the details of each modification made to the selector
     *
     * @return array<mixed> The replacements made to the selector.
     */
    public function getReplacements(): array
    {
        return $this->replacements;
    }
}
