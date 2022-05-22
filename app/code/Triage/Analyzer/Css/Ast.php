<?php

/**
 * The Ast class is herein defined.
 *
 * @link https://triage.cjwillcock.ca/
 */

declare(strict_types=1);

namespace Triage\Triage\Analyzer\Css;

use Exception;
use PhpCss;
use PhpCss\Ast\Selector\Combinator\Child;
use PhpCss\Ast\Selector\Combinator\Descendant;
use PhpCss\Ast\Selector\Combinator\Follower;
use PhpCss\Ast\Selector\Combinator\Next;
use Throwable;
use Triage\Triage\Analyzer\Css\Ast\Cleaner;

use function explode;
use function get_class;
use function preg_match;
use function showMe;
use function strlen;
use function strpos;
use function strtolower;
use function substr;

/**
 * Ast uses \PhpCss to investigate the pieces that make up each CSS selector
 */
class Ast
{
    /**
     * The details of notices, warnings and errors found in the syntax
     *
     * @var array<mixed>
     */
    private array $syntax = [
        'notices' => [],
        'warnings' => [],
        'errors' => [],
    ];

    /**
     * The details of semantics issues found in the syntax
     *
     * @var array<mixed>
     */
    private array $semantics = [
        'posh' => [],
        'microformats' => [],
        'microformats2' => [],
    ];

    /**
     * The collection of tags that make up Plain Old Simple HTML (POSH)
     */
    private array|object|null $posh = null;

    /**
     * The tokens of the well-known microformats vocabularies
     */
    private array|object|null $microformats = null;

    /**
     * The details of the file to parse
     *
     * @var array<mixed>
     */
    private array $file = [];

    /**
     * A reference to the CSS selector cleaner
     */
    private ?Cleaner $cleaner = null;

    /**
     * Catpures the file details and the set of POSH tags
     *
     * @param array<mixed> $file The details of the file to parse.
     * @param object $posh The collection of tags that are POSH.
     * @param object $microformats The classes of the well-known microformats vocabularies.
     */
    public function __construct(array $file, object $posh, object $microformats)
    {
        $this->file = $file;
        $this->posh = $posh;
        $this->microformats = $microformats;
        $this->cleaner = new Cleaner();
    }

    /**
     * Parse a selector into abstract syntax tree sequences using \PhpCss and process each sequence
     *
     * @param string $selector The selector to parse into an AST sequence.
     * @param int $line_number The line number where this selector is found in file.
     * @param string $original_selector The original selector, before any cleanup operations.
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function selector(string $selector, int $line_number, ?string $original_selector = null): void
    {
        /*
         * This function is called recursively, applying various try-to-fix replacements until AST is determined or it
         * gives up.
         */
        if ($original_selector === null) {
            $original_selector = $selector;
        }

        // The attempts to fix selectors below sometimes remove all content from the selector
        if (empty($selector)) {
            return;
        }

        try {
            $sequence_group = PhpCss::getAst($selector);

            // There is only ever one sequence in the group, because the CSS has already been parsed into selectors
            foreach ($sequence_group as $sequence) {
                $this->sequence($sequence, $line_number);
            }
        } catch (Throwable $ast_exception) {
            $this->handleSelectorException($ast_exception, $selector, $line_number, $original_selector);
        }
    }

    /**
     * Attempt to correct syntax issues in a selector and reparse the AST
     *
     * @param Exception $ast_exception The exception which occured while parsing the AST.
     * @param string $selector The selector to examine for errors.
     * @param int $line_number The line number of the selector in the file.
     * @param string $original_selector The original selector from the file, before any modifications.
     */
    private function handleSelectorException(
        Throwable $ast_exception,
        string $selector,
        int $line_number,
        string $original_selector
    ): void
    {
        $replacements_made = $this->cleaner->clean($selector);

        if (0 < $replacements_made) {
            $this->recordReplacements($line_number);

            $this->selector($this->cleaner->getCleanedSelector(), $line_number, $original_selector);

            return;
        }

        // Unable to clean bad selector, ignore it
        $this->syntax['errors'][$line_number][] = [
            'before' => $original_selector,
            'after' => $selector,
            'exception' => $ast_exception->getMessage(),
        ];
    }

    /**
     * Capture replacements made by the Cleaner into the report supplied in the analysis
     *
     * @param int $line_number The line number of the selectore.
     */
    private function recordReplacements(int $line_number): void
    {
        foreach ($this->cleaner->getReplacements() as $replacement_type => $replacements) {
            [$level, $type] = explode('/', $replacement_type);

            foreach ($replacements as $replacement) {
                $this->syntax[$level][$type][$line_number][] = $replacement;
            }
        }
    }

    /**
     * Decompose a selector's AST into it's constituent tokens
     *
     * @param object $sequence An AST sequence.
     * @param int $line_number The line number of this selector.
     *
     * @throws Exception Thrown when a combinator that has no defined interpretation here, is encountered.
     */
    private function sequence(object $sequence, int $line_number): void
    {
        foreach ($sequence->simples as $simple) {
            $this->simple($simple, $line_number);
        }

        if ($sequence->combinator === null) {
            return;
        }

        switch ($sequence->combinator::class) {
            case Child::class:
            case Descendant::class:
            case Next::class:
            case Follower::class:
                $this->sequence($sequence->combinator->sequence, $line_number);

                break;

            default:
                showMe($sequence->combinator);
                throw new Exception("Unknown combinator type: " . get_class($sequence->combinator));
        }
    }

    /**
     * Consider a single token from the AST and delegate for further analysis
     *
     * @param object $simple A simple token from the AST.
     * @param int $line_number The line number where this token is found.
     *
     * @throws Exception Thrown when a simple type that has no defined interpretation here, is encountered.
     */
    private function simple(object $simple, int $line_number): void
    {
        // This function should have $parameter = false in the last position

        $namespace_parts = explode('\\', $simple::class);

        if ($namespace_parts[2] == 'Value') {
            // Nothing doing
            // 'PhpCss\Ast\Value\Position': :nth-child(4n+1)
            // 'PhpCss\Ast\Value\Language': :lang(he-il)
            $this->simpleValue($namespace_parts[3], $simple, $line_number);

            return;
        }

        if ($namespace_parts[2] == 'Selector') {
            $this->simpleSelector($namespace_parts[4], $simple, $line_number);

            return;
        }

        throw new Exception("Unknown simple type in {$this->file['scan_path']} @ $line_number: " . $simple::class);
    }

    /**
     * Act on simple value tokens
     *
     * At this time, no action is taken regardless of which simple value type is encountered.
     *
     * @param string $value_type The type of value in the AST.
     * @param object $simple The single token of the selctor AST.
     * @param int $line_number The line number of this selector.
     *
     * @throws Exception Thrown when a simple value type that has no defined interpretation here, is encountered.
     */
    private function simpleValue(string $value_type, object $simple, int $line_number): void
    {
        switch ($value_type) {
            case 'Position':
            case 'Language':
                // Nothing doing
                break;

            default:
                throw new Exception(
                    "Unknown simple value type in {$this->file['scan_path']} @ $line_number: " . $simple::class
                );
        }
    }

    /**
     * Act on simple selector tokens
     *
     * @param string $selector_type The type of selector token in the AST.
     * @param object $simple The single token of the selctor AST.
     * @param int $line_number The line number of this selector.
     *
     * @throws Exception Thrown when a simple selector type that has no defined interpretation here, is encountered.
     */
    private function simpleSelector(string $selector_type, object $simple, int $line_number): void
    {
        switch ($selector_type) {
            case 'Type':
            case 'ClassName':
            case 'Id':
            case 'Attribute':
            case 'PseudoClass':
            case 'PseudoElement':
            case 'Universal':
                $method = "simpleSelector$selector_type";
                $this->$method($simple, $line_number);

                break;

            default:
                throw new Exception(
                    "Unknown simple selector type ($selector_type) in {$this->file['scan_path']} @ $line_number: "
                        . $simple::class
                );
        }
    }

    /**
     * Act on element tokens found in the selector AST
     *
     * Capture elements in selectors that are not POSH
     *
     * @param object $simple The element token.
     * @param int $line_number The line number of the selector.
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function simpleSelectorType(object $simple, int $line_number): void
    {
        // Element
        // e.g.: a; span; html
        // nothing doing
        $element = $simple->elementName;

        if (isset($this->posh->$element)) {
            return;
        }

        $this->semantics['posh'][$simple->elementName][] = $line_number;
    }

    /**
     * Act on class tokens found in the selector AST
     *
     * Capture microformats classes used in selectors
     *
     * @param object $simple The class token.
     * @param int $line_number The line number of the selector.
     *
     * @SuppressWarnings(PHPMD)
     */
    private function simpleSelectorClassName(object $simple, int $line_number): void
    {
        // Class <- what we are here to investigate
        $token = $simple->className;
        $normalized_token = strtolower($token);

        if (isset($this->microformats->microformats->$normalized_token)) {
            $this->semantics['microformats'][$normalized_token][] = $line_number;

            //showMe($target['scan_path'] . ' @ ' . $line_number . ': using microformats token ' . $normalized_token);
            return;
        }

        if (! preg_match('/^(?:[a-z]{1,2})-.*/i', $token)) {
            return;
        }

        if (preg_match('/^(?:h|e|u|dt|p)-.*/i', $token)) {
            /*
             * showMe(
             *     $target['scan_path'] . ' @ ' . $line_number . ': using microformats2 prefixing ' . $normalized_token
             * );
             */
            $this->microformats2Semantics($normalized_token, $line_number);

            return;
        }

        $single_or_double_letter_prefix = substr($normalized_token, 0, strpos($normalized_token, '-'));

        /*
         * showMe(
         *    "Speculative mf2 token format: \"" . $single_or_double_letter_prefix . "-*\" matching \"$token\" used in "
         *         . "{$target['scan_path']} @ line $line_number"
         * );
         */

        if (strlen($single_or_double_letter_prefix) == 2) {
            $this->semantics['microformats2']['prefix-format']['double-letter'][$single_or_double_letter_prefix][] = [
                'line_number' => $line_number,
                'token' => $token,
            ];

            return;
        }

        $this->semantics['microformats2']['prefix-format']['single-letter'][$single_or_double_letter_prefix][] = [
            'line_number' => $line_number,
            'token' => $token,
        ];
    }

    /**
     * Capture usage of microformats2 tokens in Css
     *
     * @param string $normalized_token The CSS token.
     * @param int $line_number The line number.
     */
    private function microformats2Semantics(string $normalized_token, int $line_number): void
    {
        // Check for well-known mf2 vocabulary tokens
        if (isset($this->microformats->microformats2->$normalized_token)) {
            //showMe("Well-known mf2 token: \"$normalized_token\" used in {$target['scan_path']} @ line $line_number");
            $this->semantics['microformats2']['well-known'][$normalized_token][] = $line_number;

            return;
        }

        /*
         * showMe(
         *     "Well-known mf2 token format: \"" . substr($normalized_token, 0, 1) . "-*\" matching \"$token\" used in "
         *         . "{$target['scan_path']} @ line $line_number"
         * );
         */
        $this->semantics['microformats2']['well-known-format'][$normalized_token][] = $line_number;
    }

    /**
     * Act on Id tokens found in the selector AST
     *
     * @param object $simple The Id token.
     * @param int $line_number The line number of the selector.
     *
     * @SuppressWarnings(PHPMD)
     */
    private function simpleSelectorId(object $simple, int $line_number): void
    {
        // Not good to use microformats vocab in IDs either? (warn)
        // showMe('#' . $simple->id);
    }

    /**
     * Act on attribute tokens found in the selector AST
     *
     * @param object $simple The attribute token.
     * @param int $line_number The line number of the selector.
     *
     * @SuppressWarnings(PHPMD)
     */
    private function simpleSelectorAttribute(object $simple, int $line_number): void
    {
        // The following should be done once, outside the loop, for how the library is setup now
        // For forward compatibility, leaving as is for the time being
        // $reflection = new \ReflectionClass(get_class($simple));
        // $constants = array_flip($reflection->getConstants());
        // showMe("[$simple->name {$constants[$simple->match]} $simple->literal]");
    }

    /**
     * Act on pseudo-class tokens found in the selector AST
     *
     * @param object $simple The pseudo-class token.
     * @param int $line_number The line number of the selector.
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function simpleSelectorPseudoClass(object $simple, int $line_number): void
    {
        //showMe(':' . $simple->name);
        if ($simple->parameter === null) {
            return;
        }

        //$this->_selectorASTSimple($simple->parameter, $line_number, true);
        $this->simple($simple->parameter, $line_number);
    }

    /**
     * Act on pseudo-element tokens found in the selector AST
     *
     * @param object $simple The pseudo-element token.
     * @param int $line_number The line number of the selector.
     *
     * @SuppressWarnings(PHPMD)
     */
    private function simpleSelectorPseudoElement(object $simple, int $line_number): void
    {
        //showMe("::$simple->name");
    }

    /**
     * Act on universal tokens found in the selector AST
     *
     * @param object $simple The universal token.
     * @param int $line_number The line number of the selector.
     *
     * @SuppressWarnings(PHPMD)
     */
    private function simpleSelectorUniversal(object $simple, int $line_number): void
    {
        // showMe('*');
    }

    /**
     * Provide the details of syntax notices, warnings and errors
     *
     * @return array<mixed> The syntax details.
     */
    public function getSyntax(): array
    {
        return $this->syntax;
    }

    /**
     * Provide the details of semantics compatibility issues
     *
     * @return array<mixed> The semantics details.
     */
    public function getSemantics(): array
    {
        return $this->semantics;
    }
}
