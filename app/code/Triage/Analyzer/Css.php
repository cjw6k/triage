<?php

/**
 * The Css class is herein defined.
 *
 * @link https://triage.cjwillcock.ca/
 */

declare(strict_types=1);

namespace Triage\Triage\Analyzer;

use Exception;
use Sabberworm\CSS\CSSList\AtRuleBlockList;
use Sabberworm\CSS\CSSList\Document;
use Sabberworm\CSS\CSSList\KeyFrame;
use Sabberworm\CSS\OutputFormat;
use Sabberworm\CSS\Parser;
use Sabberworm\CSS\Property\Charset;
use Sabberworm\CSS\Property\Import;
use Sabberworm\CSS\RuleSet\AtRuleSet;
use Sabberworm\CSS\RuleSet\DeclarationBlock;
use Triage\Triage\Analyzer\Css\Ast;

use function file_get_contents;
use function is_string;
use function sprintf;
use function strlen;

use const DIRECTORY_SEPARATOR;

/**
 * Css parses CSS files into selectors and returns a detailed accounting
 */
class Css
{
    /**
     * The CSS file to analyze
     *
     * @var array<string>
     */
    private array $file = [];

    /**
     * Statistics of the composition of the CSS file
     *
     * @var array<mixed>
     */
    private array $stats = [];

    /**
     * The full set of valid selectors found in the CSS file
     *
     * @var array<mixed>
     */
    private array $selectors_by_line = [];

    /**
     * The full set of imports found in the CSS file
     *
     * @var array<mixed>
     */
    private array $imports_by_line = [];

    /**
     * The full set of font declarations found in the CSS file
     *
     * @var array<mixed>
     */
    private array $fonts_by_line = [];

    /**
     * A reference to the AST analyzer
     */
    private array|object|null $ast = null;

    /**
     * Capture the file details and the set of POSH tokens
     *
     * @param array<string> $file The file to analyze.
     * @param object $posh The POSH tokens.
     * @param object $microformats The microformats vocabularies.
     */
    public function __construct(array $file, object $posh, object $microformats)
    {
        $this->file = $file;
        $this->ast = new Ast($file, $posh, $microformats);
    }

    /**
     * Perform analysis on a single CSS file
     *
     * @return array<mixed> The details of the analyzis.
     */
    public function analyze(): array
    {
        $css = file_get_contents($this->file['path'] . DIRECTORY_SEPARATOR . $this->file['filename']);
        $parser = new Parser($css);

        // The parsing library sometimes generates PHP Notices when bad syntax is encountered
        $tree = @$parser->parse();

        $this->collectStatistics($tree, strlen($css));

        $this->analyzeHelper($tree);

        return [
            'mime_type' => $this->file['mime_type'],
            'scan_path' => $this->file['scan_path'],
            'stats' => $this->stats,
            'selectors_by_line' => $this->selectors_by_line,
            'imports_by_line' => $this->imports_by_line,
            'fonts_by_line' => $this->fonts_by_line,
            'syntax' => $this->ast->getSyntax(),
            'semantics' => $this->ast->getSemantics(),
        ];
    }

    /**
     * Determine statistics about the composition of this CSS file
     *
     * @param Document $tree The parse tree of the CSS file.
     * @param int $css_length The length of the CSS file.
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    private function collectStatistics(Document $tree, int $css_length): void
    {
        // This will leave optional spaces around combinators; is not an ideal minification
        $minified = $tree->render(OutputFormat::createCompact());

        $this->stats = [
            'size' => $css_length,
            'minified_size' => strlen($minified),
        ];
        $this->stats['whitespace'] = $this->stats['size'] - $this->stats['minified_size'];
        $this->stats['whitespace_percent'] = ! empty($this->stats['size'])
            ? sprintf('%0.2f', 100 * ($this->stats['whitespace'] / $this->stats['size']))
            : '0.00';
    }

    /**
     * Analyze the parse tree
     *
     * @param Document $tree The parse tree of the CSS file.
     *
     * @throws Exception
     */
    private function analyzeHelper(Document $tree): void
    {
        foreach ($tree->getContents() as $css_list_item) {
            $css_list_class = $css_list_item::class;

            switch ($css_list_class) {
                case DeclarationBlock::class:
                    $this->declarationBlock($css_list_item);

                    break;

                case AtRuleBlockList::class:
                    // e.g. @media
                    $this->atRuleBlockList($css_list_item, $css_list_class);

                    break;

                case KeyFrame::class:
                    // e.g. @keyframe
                    // nothing doing
                    break;

                case Charset::class:
                    // e.g. @charset
                    // nothing doing
                    break;

                case Import::class:
                    // e.g. @import
                    $this->imports_by_line[$css_list_item->getLineNo()]
                        = $css_list_item->getLocation()->getURL()->getString();

                    // Is it non-local?
                    break;

                case AtRuleSet::class:
                    // e.g. @font-face, or any other unrecognized @-rule
                    $this->atRuleSet($css_list_item, $css_list_class);

                    break;

                default:
                    //$reflector = new \ReflectionClass($css_list_class);
                    //showMe($reflector->getMethods());
                    echo "{$this->file['scan_path']} : $css_list_class\n";
            }
        }
    }

    /**
     * Consider a declaration block from the parse tree
     *
     * Declaration blocks contain the selectors. For each selector, a call is made to the AST class
     * to continue analysis.
     *
     * @param object $declaration_block The declaration block from the parse tree.
     * @param string $section The section of the CSS file (the nested @rule, e.g. @media).
     */
    private function declarationBlock(object $declaration_block, string $section = 'root'): void
    {
        foreach ($declaration_block->getSelectors() as $selector) {
            // e.g. "a > span"
            $this->selectors_by_line[$section][$declaration_block->getLineNo()][] = $selector->getSelector();

            $this->ast->selector($selector->getSelector(), $declaration_block->getLineNo());
        }
    }

    /**
     * Consider an @rule-set from the parse tree
     *
     * @param object $rule_set The @rule-set from the parse tree.
     * @param string $css_list_class The class of this @rule-set.
     */
    private function atRuleSet(object $rule_set, string $css_list_class): void
    {
        switch ($rule_set->atRuleName()) {
            case 'font-face':
                $font_families = $rule_set->getRulesAssoc('font-family');

                if (! empty($font_families)) {
                    foreach ($font_families as $font_family) {
                        // Is it a local resource (URL)?
                        if (is_string($font_family->getValue())) {
                            $this->fonts_by_line[$rule_set->getLineNo()] = $font_family->getValue();
                            continue;
                        }

                        $this->fonts_by_line[$rule_set->getLineNo()] = $font_family->getValue()->getString();
                    }
                }

                break;

            case '-ms-viewport':
            case 'viewport':
            case 'page':
                // nothing doing
                break;

            default:
                // $reflector = new \ReflectionClass($css_list_class);
                // showMe($target['scan_path'] . " " . $rule_set->getLineNo() . " - @" . $rule_set->atRuleName());
                // showMe($reflector->getMethods());
                echo "{$this->file['scan_path']} : $css_list_class\n";
        }
    }

    /**
     * Consider an @rule-block-list from the parse tree
     *
     * @param object $block_list The block list from the parse tree.
     * @param string $css_list_class The class of this block list.
     *
     * @throws Exception Thrown when an block list class with that has no defined interpretation here, is encountered.
     */
    private function atRuleBlockList(object $block_list, string $css_list_class): void
    {
        foreach ($block_list->getContents() as $css_list_item) {
            switch ($css_list_item::class) {
                case DeclarationBlock::class:
                    $this->declarationBlock($css_list_item, $block_list->atRuleArgs());

                    break;

                case AtRuleSet::class:
                    $this->atRuleSet($css_list_item, $css_list_class);

                    break;

                case KeyFrame::class:
                    // nothing doing
                    break;

                default:
                    //$reflector = new \ReflectionClass(get_class($css_atrule_item));
                    //showMe($reflector->getMethods());
                    // showMe($target['scan_path']);
                    // showMe($css_list->getLineNo());
                    // showMe(get_class($css_atrule_item));
                    throw new Exception(
                        "Unknown @rule block list item " . $css_list_item::class . " in {$this->file['scan_path']} @ "
                            . "line " . $block_list->getLineNo() . "\n"
                    );
            }
        }
    }
}
