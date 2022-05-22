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
    private array $_file = [];

    /**
     * Statistics of the composition of the CSS file
     *
     * @var array<mixed>
     */
    private array $_stats = [];

    /**
     * The full set of valid selectors found in the CSS file
     *
     * @var array<mixed>
     */
    private array $_selectors_by_line = [];

    /**
     * The full set of imports found in the CSS file
     *
     * @var array<mixed>
     */
    private array $_imports_by_line = [];

    /**
     * The full set of font declarations found in the CSS file
     *
     * @var array<mixed>
     */
    private array $_fonts_by_line = [];

    /**
     * A reference to the AST analyzer
     */
    private array|object|null $_ast = null;

    /**
     * Capture the file details and the set of POSH tokens
     *
     * @param array<string> $file The file to analyze.
     * @param object $posh The POSH tokens.
     * @param object $microformats The microformats vocabularies.
     */
    public function __construct(array $file, object $posh, object $microformats)
    {
        $this->_file = $file;
        $this->_ast = new Css\Ast($file, $posh, $microformats);
    }

    /**
     * Perform analysis on a single CSS file
     *
     * @return array<mixed> The details of the analyzis.
     */
    public function analyze(): array
    {
        $css = file_get_contents($this->_file['path'] . DIRECTORY_SEPARATOR . $this->_file['filename']);
        $parser = new Parser($css);

        // The parsing library sometimes generates PHP Notices when bad syntax is encountered
        $tree = @$parser->parse();

        $this->_collectStatistics($tree, strlen($css));

        $this->_analyze($tree);

        return [
            'mime_type' => $this->_file['mime_type'],
            'scan_path' => $this->_file['scan_path'],
            'stats' => $this->_stats,
            'selectors_by_line' => $this->_selectors_by_line,
            'imports_by_line' => $this->_imports_by_line,
            'fonts_by_line' => $this->_fonts_by_line,
            'syntax' => $this->_ast->getSyntax(),
            'semantics' => $this->_ast->getSemantics(),
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
    private function _collectStatistics(Document $tree, int $css_length): void
    {
        // This will leave optional spaces around combinators; is not an ideal minification
        $minified = $tree->render(OutputFormat::createCompact());

        $this->_stats = [
            'size' => $css_length,
            'minified_size' => strlen($minified),
        ];
        $this->_stats['whitespace'] = $this->_stats['size'] - $this->_stats['minified_size'];
        $this->_stats['whitespace_percent'] = ! empty($this->_stats['size'])
            ? sprintf('%0.2f', 100 * ($this->_stats['whitespace'] / $this->_stats['size']))
            : '0.00';
    }

    /**
     * Analyze the parse tree
     *
     * @param Document $tree The parse tree of the CSS file.
     *
     * @throws Exception
     */
    private function _analyze(Document $tree): void
    {
        foreach ($tree->getContents() as $css_list_item) {
            $css_list_class = $css_list_item::class;

            switch ($css_list_class) {
                case DeclarationBlock::class:
                    $this->_declarationBlock($css_list_item);

                    break;

                case AtRuleBlockList::class:
                    // e.g. @media
                    $this->_atRuleBlockList($css_list_item, $css_list_class);

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
                    $this->_imports_by_line[$css_list_item->getLineNo()] = $css_list_item->getLocation()->getURL()->getString();

                    // Is it non-local?
                    break;

                case AtRuleSet::class:
                    // e.g. @font-face, or any other unrecognized @-rule
                    $this->_atRuleSet($css_list_item, $css_list_class);

                    break;

                default:
                    //$reflector = new \ReflectionClass($css_list_class);
                    //showMe($reflector->getMethods());
                    echo "{$this->_file['scan_path']} : $css_list_class\n";
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
    private function _declarationBlock(object $declaration_block, string $section = 'root'): void
    {
        foreach ($declaration_block->getSelectors() as $selector) {
            // e.g. "a > span"
            $this->_selectors_by_line[$section][$declaration_block->getLineNo()][] = $selector->getSelector();

            $this->_ast->selector($selector->getSelector(), $declaration_block->getLineNo());
        }
    }

    /**
     * Consider an @rule-set from the parse tree
     *
     * @param object $rule_set The @rule-set from the parse tree.
     * @param string $css_list_class The class of this @rule-set.
     */
    private function _atRuleSet(object $rule_set, string $css_list_class): void
    {
        switch ($rule_set->atRuleName()) {
            case 'font-face':
                $font_families = $rule_set->getRulesAssoc('font-family');

                if (! empty($font_families)) {
                    foreach ($font_families as $font_family) {
                        // Is it a local resource (URL)?
                        if (is_string($font_family->getValue())) {
                            $this->_fonts_by_line[$rule_set->getLineNo()] = $font_family->getValue();
                            continue;
                        }

                        $this->_fonts_by_line[$rule_set->getLineNo()] = $font_family->getValue()->getString();
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
                echo "{$this->_file['scan_path']} : $css_list_class\n";
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
    private function _atRuleBlockList(object $block_list, string $css_list_class): void
    {
        foreach ($block_list->getContents() as $css_list_item) {
            switch ($css_list_item::class) {
                case DeclarationBlock::class:
                    $this->_declarationBlock($css_list_item, $block_list->atRuleArgs());

                    break;

                case AtRuleSet::class:
                    $this->_atRuleSet($css_list_item, $css_list_class);

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
                    throw new Exception("Unknown @rule block list item " . $css_list_item::class . " in {$this->_file['scan_path']} @ line " . $block_list->getLineNo() . "\n");
            }
        }
    }
}
