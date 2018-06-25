<?php
/**
 * The Css class is herein defined.
 *
 * @copyright (C) 2018 by the contributors
 *
 * LICENSE: See the /LICENSE.md file for details (MIT)
 *
 * @package	Triage
 * @author	Christopher James Willcock <cjwillcock@ieee.org>
 * @link	https://triage.cjwillcock.ca/
 */

declare(strict_types=1);

namespace Triage\Triage\Analyzer;

/**
 * Css parses CSS files into selectors and returns a detailed accounting
 */
class Css
{

	/**
	 * The CSS file to analyze
	 *
	 * @var string[]
	 */
	private $_file = array();

	/**
	 * Statistics of the composition of the CSS file
	 *
	 * @var mixed[]
	 */
	private $_stats = array();

	/**
	 * The full set of valid selectors found in the CSS file
	 *
	 * @var mixed[]
	 */
	private $_selectors_by_line = array();

	/**
	 * The full set of imports found in the CSS file
	 *
	 * @var mixed[]
	 */
	private $_imports_by_line = array();

	/**
	 * The full set of font declarations found in the CSS file
	 *
	 * @var mixed[]
	 */
	private $_fonts_by_line = array();

	/**
	 * A reference to the AST analyzer
	 *
	 * @var mixed[]
	 */
	private $_ast = null;

	/**
	 * Capture the file details and the set of POSH tokens
	 *
	 * @param string[] $file The file to analyze.
	 * @param object   $posh The POSH tokens.
	 */
	public function __construct(array $file, object $posh)
	{
		$this->_file = $file;
		$this->_ast = new Css\Ast($file, $posh);
	}

	/**
	 * Perform analysis on a single CSS file
	 *
	 * @return mixed[] The details of the analyzis.
	 */
	public function analyze() : array
	{
		$css = file_get_contents($this->_file['path'] . DIRECTORY_SEPARATOR . $this->_file['filename']);
		$parser = new \Sabberworm\CSS\Parser($css);

		// The parsing library sometimes generates PHP Notices when bad syntax is encountered
		// An @ could appear here, (where a pull request to upstream is made first)
		$tree = $parser->parse();

		$this->_collectStatistics($tree, strlen($css));

		$this->_analyze($tree);

		return array(
			'mime_type'         => $this->_file['mime_type'],
			'scan_path'         => $this->_file['scan_path'],
			'stats'             => $this->_stats,
			'selectors_by_line' => $this->_selectors_by_line,
			'imports_by_line'   => $this->_imports_by_line,
			'fonts_by_line'     => $this->_fonts_by_line,
			'syntax'            => $this->_ast->getSyntax(),
			'semantics'         => $this->_ast->getSemantics(),
		);
	}

	/**
	 * Determine statistics about the composition of this CSS file
	 *
	 * @param \Sabberworm\CSS\CSSList\Document $tree       The parse tree of the CSS file.
	 * @param integer                          $css_length The length of the CSS file.
	 *
	 * @return void
	 *
	 * @SuppressWarnings(PHPMD.StaticAccess)
	 */
	private function _collectStatistics(\Sabberworm\CSS\CSSList\Document $tree, int $css_length)
	{
		// This will leave optional spaces around combinators; is not an ideal minification
		$minified = $tree->render(\Sabberworm\CSS\OutputFormat::createCompact());

		$this->_stats = array(
			'size' => $css_length,
			'minified_size' => strlen($minified),
		);
		$this->_stats['whitespace'] = $this->_stats['size'] - $this->_stats['minified_size'];
		$this->_stats['whitespace_percent'] = !empty($this->_stats['size']) ? sprintf('%0.2f', 100 * ($this->_stats['whitespace'] / $this->_stats['size'])) : '0.00';
	}

	/**
	 * Analyze the parse tree
	 *
	 * @param \Sabberworm\CSS\CSSList\Document $tree The parse tree of the CSS file.
	 *
	 * @return void
	 */
	private function _analyze(\Sabberworm\CSS\CSSList\Document $tree)
	{
		foreach($tree->getContents() as $css_list_item){
			$css_list_class = get_class($css_list_item);
			switch($css_list_class){
				case 'Sabberworm\CSS\RuleSet\DeclarationBlock':
					$this->_declarationBlock($css_list_item);
					break;

				case 'Sabberworm\CSS\CSSList\AtRuleBlockList':
					// e.g. @media
					$this->_atRuleBlockList($css_list_item, $css_list_class);
					break;

				case 'Sabberworm\CSS\CSSList\KeyFrame':
					// e.g. @keyframe
					// nothing doing
					break;

				case 'Sabberworm\CSS\Property\Charset':
					// e.g. @charset
					// nothing doing
					break;

				case 'Sabberworm\CSS\Property\Import':
					// e.g. @import
					$this->_imports_by_line[$css_list_item->getLineNo()] = $css_list_item->getLocation()->getURL()->getString();
					// Is it non-local?
					break;

				case 'Sabberworm\CSS\RuleSet\AtRuleSet':
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
	 * @param string $section           The section of the CSS file (the nested @rule, e.g. @media).
	 *
	 * @return void
	 */
	private function _declarationBlock(object $declaration_block, string $section = 'root')
	{
		foreach($declaration_block->getSelectors() as $selector){
			// e.g. "a > span"
			$this->_selectors_by_line[$section][$declaration_block->getLineNo()][] = $selector->getSelector();

			$this->_ast->selector($selector->getSelector(), $declaration_block->getLineNo());
		}
	}

	/**
	 * Consider an @rule-set from the parse tree
	 *
	 * @param object $rule_set       The @rule-set from the parse tree.
	 * @param object $css_list_class The class of this @rule-set.
	 *
	 * @return void
	 */
	private function _atRuleSet(object $rule_set, object $css_list_class)
	{
		switch($rule_set->atRuleName()){
			case 'font-face':
				$font_families = $rule_set->getRulesAssoc('font-family');
				if(!empty($font_families)){
					foreach($font_families as $font_family){
						// Is it a local resource (URL)?
						if(is_string($font_family->getValue())){
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
	 * @param object $block_list     The block list from the parse tree.
	 * @param object $css_list_class The class of this block list.
	 *
	 * @throws \Exception Thrown when an block list class with that has no defined interpretation here, is encountered.
	 *
	 * @return void
	 */
	private function _atRuleBlockList(object $block_list, object $css_list_class)
	{
		foreach($block_list->getContents() as $css_list_item){
			switch(get_class($css_list_item)){
				case 'Sabberworm\CSS\RuleSet\DeclarationBlock':
					$this->_declarationBlock($css_list_item, $block_list->atRuleArgs());
					break;

				case 'Sabberworm\CSS\RuleSet\AtRuleSet':
					$this->_atRuleSet($css_list_item, $css_list_class);
					break;

				case 'Sabberworm\CSS\CSSList\KeyFrame':
					// nothing doing
					break;

				default:
					//$reflector = new \ReflectionClass(get_class($css_atrule_item));
					//showMe($reflector->getMethods());
					// showMe($target['scan_path']);
					// showMe($css_list->getLineNo());
					// showMe(get_class($css_atrule_item));
					throw new \Exception("Unknown @rule block list item " . get_class($css_list_item) . " in {$this->_file['scan_path']} @ line " . $block_list->getLineNo() . "\n");
			}
		}
	}

}
