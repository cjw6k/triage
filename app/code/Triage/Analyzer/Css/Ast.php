<?php
/**
 * The Ast class is herein defined.
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

namespace Triage\Triage\Analyzer\Css;

/**
 * Ast uses \PhpCss to investigate the pieces that make up each CSS selector
 */
class Ast
{

	/**
	 * The details of notices, warnings and errors found in the syntax
	 *
	 * @var mixed[]
	 */
	private $_syntax = array(
		'notices' => array(),
		'warnings' => array(),
		'errors' => array(),
	);

	/**
	 * The details of POSH issues found in the syntax
	 *
	 * @var mixed[]
	 */
	private $_semantics = array(
		'posh' => array(),
	);

	/**
	 * The collection of tags that make up Plain Old Simple HTML (POSH)
	 *
	 * @var mixed[]
	 */
	private $_posh = null;

	/**
	 * The details of the file to parse
	 *
	 * @var mixed[]
	 */
	private $_file = array();

	/**
	 * A reference to the CSS selector cleaner
	 *
	 * @var Ast\Cleaner
	 */
	private $_cleaner = null;

	/**
	 * Catpures the file details and the set of POSH tags
	 *
	 * @param mixed[] $file The details of the file to parse.
	 * @param object  $posh The collection of tags that are POSH.
	 */
	public function __construct(array $file, object $posh)
	{
		$this->_file = $file;
		$this->_posh = $posh;
		$this->_cleaner = new Ast\Cleaner();
	}

	/**
	 * Parse a selector into abstract syntax tree sequences using \PhpCss and process each sequence
	 *
	 * @param string  $selector          The selector to parse into an AST sequence.
	 * @param integer $line_number       The line number where this selector is found in file.
	 * @param string  $original_selector The original selector, before any cleanup operations.
	 *
	 * @return void
	 *
	 * @SuppressWarnings(PHPMD.StaticAccess)
	 */
	public function selector(string $selector, int $line_number, string $original_selector = null)
	{
		// This function is called recursively, applying various try-to-fix replacements until AST is determined or it gives up
		if(null === $original_selector){
			$original_selector = $selector;
		}

		// The attempts to fix selectors below sometimes remove all content from the selector
		if(empty($selector)){
			return;
		}

		try{
			$sequence_group = \PhpCss::getAst($selector);

			// There is only ever one sequence in the group, because the CSS has already been parsed into selectors
			foreach($sequence_group as $sequence){
				$this->_sequence($sequence, $line_number);
			}
		} catch(\Exception $ast_exception){
			$this->_handleSelectorException($ast_exception, $selector, $line_number, $original_selector);
		}
	}

	/**
	 * Attempt to correct syntax issues in a selector and reparse the AST
	 *
	 * @param \Exception $ast_exception     The exception which occured while parsing the AST.
	 * @param string     $selector          The selector to examine for errors.
	 * @param integer	 $line_number	    The line number of the selector in the file.
	 * @param string	 $original_selector The original selector from the file, before any modifications.
	 *
	 * @return void
	 */
	private function _handleSelectorException(\Exception $ast_exception, string $selector, int $line_number, string $original_selector)
	{
		$replacements_made = $this->_cleaner->clean($selector);
		if(0 < $replacements_made){
			$this->_recordReplacements($line_number);

			$this->selector($this->_cleaner->getCleanedSelector(), $line_number, $original_selector);
			return;
		}

		// Unable to clean bad selector, ignore it
		$this->_syntax['errors'][$line_number][] = array(
			'before' => $original_selector,
			'after' => $selector,
			'exception' => $ast_exception->getMessage(),
		);
	}

	/**
	 * Capture replacements made by the Cleaner into the report supplied in the analysis
	 *
	 * @param integer $line_number The line number of the selectore.
	 *
	 * @return void
	 */
	private function _recordReplacements(int $line_number)
	{
		foreach($this->_cleaner->getReplacements() as $replacement_type => $replacements){
			list($level, $type) = explode('/', $replacement_type);
			foreach($replacements as $replacement){
				$this->_syntax[$level][$type][$line_number][] = $replacement;
			}
		}
	}

	/**
	 * Decompose a selector's AST into it's constituent tokens
	 *
	 * @param object  $sequence    An AST sequence.
	 * @param integer $line_number The line number of this selector.
	 *
	 * @throws \Exception Thrown when a combinator that has no defined interpretation here, is encountered.
	 *
	 * @return void
	 */
	private function _sequence(object $sequence, int $line_number)
	{
		foreach($sequence->simples as $simple){
			$this->_simple($simple, $line_number);
		}

		if(null === $sequence->combinator){
			return;
		}

		// These are separated out for later statistical tracking (maybe)
		switch(get_class($sequence->combinator)){
			case 'PhpCss\Ast\Selector\Combinator\Child':
				$this->_sequence($sequence->combinator->sequence, $line_number);
				break;

			case 'PhpCss\Ast\Selector\Combinator\Descendant':
				$this->_sequence($sequence->combinator->sequence, $line_number);
				break;

			case 'PhpCss\Ast\Selector\Combinator\Next':
				$this->_sequence($sequence->combinator->sequence, $line_number);
				break;

			case 'PhpCss\Ast\Selector\Combinator\Follower':
				$this->_sequence($sequence->combinator->sequence, $line_number);
				break;

			default:
				showMe($sequence->combinator);
				throw new \Exception("Unknown combinator type: " . get_class($sequence->combinator));
		}
	}

	/**
	 * Consider a single token from the AST and delegate for further analysis
	 *
	 * @param object  $simple      A simple token from the AST.
	 * @param integer $line_number The line number where this token is found.
	 *
	 * @throws \Exception Thrown when a simple type that has no defined interpretation here, is encountered.
	 *
	 * @return void
	 */
	private function _simple(object $simple, int $line_number)
	{
		// This function should have $parameter = false in the last position

		$namespace_parts = explode('\\', get_class($simple));

		if('Value' == $namespace_parts[2]){
			// Nothing doing
			// 'PhpCss\Ast\Value\Position': :nth-child(4n+1)
			// 'PhpCss\Ast\Value\Language': :lang(he-il)
			$this->_simpleValue($namespace_parts[3], $simple, $line_number);
			return;
		}

		if('Selector' == $namespace_parts[2]){
			$this->_simpleSelector($namespace_parts[4], $simple, $line_number);
			return;
		}

		throw new \Exception("Unknown simple type in {$this->_file['scan_path']} @ $line_number: " . get_class($simple));
	}

	/**
	 * Act on simple value tokens
	 *
	 * At this time, no action is taken regardless of which simple value type is encountered.
	 *
	 * @param string  $value_type  The type of value in the AST.
	 * @param object  $simple      The single token of the selctor AST.
	 * @param integer $line_number The line number of this selector.
	 *
	 * @throws \Exception Thrown when a simple value type that has no defined interpretation here, is encountered.
	 *
	 * @return void
	 */
	private function _simpleValue(string $value_type, object $simple, int $line_number)
	{
		switch($value_type){
			case 'Position':
				// Nothing doing
				break;

			case 'Language':
				// Nothing doing
				break;

			default:
				throw new \Exception("Unknown simple value type in {$this->_file['scan_path']} @ $line_number: " . get_class($simple));
		}
	}

	/**
	 * Act on simple selector tokens
	 *
	 * @param string  $selector_type The type of selector token in the AST.
	 * @param object  $simple        The single token of the selctor AST.
	 * @param integer $line_number   The line number of this selector.
	 *
	 * @throws \Exception Thrown when a simple selector type that has no defined interpretation here, is encountered.
	 *
	 * @return void
	 */
	private function _simpleSelector(string $selector_type, object $simple, int $line_number)
	{
		switch($selector_type){
			case 'Type':
			case 'ClassName':
			case 'Id':
			case 'Attribute':
			case 'PseudoClass':
			case 'PseudoElement':
			case 'Universal':
				$method = "_simpleSelector$selector_type";
				$this->$method($simple, $line_number);
				break;

			default:
				throw new \Exception("Unknown simple selector type ($selector_type) in {$this->_file['scan_path']} @ $line_number: " . get_class($simple));
		}
	}

	/**
	 * Act on element tokens found in the selector AST
	 *
	 * Capture elements in selectors that are not POSH
	 *
	 * @param object  $simple      The element token.
	 * @param integer $line_number The line number of the selector.
	 *
	 * @return void
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function _simpleSelectorType(object $simple, int $line_number)
	{
		// Element
		// e.g.: a; span; html
		// nothing doing
		$element = $simple->elementName;
		if(!isset($this->_posh->$element)){
			$this->_semantics['posh'][$simple->elementName][] = $line_number;
		}
	}

	/**
	 * Act on class tokens found in the selector AST
	 *
	 * Capture microformats classes used in selectors
	 *
	 * @param object  $simple      The class token.
	 * @param integer $line_number The line number of the selector.
	 *
	 * @return void
	 *
	 * @SuppressWarnings(PHPMD)
	 */
	private function _simpleSelectorClassName(object $simple, int $line_number)
	{
		// Class <- what we are here to investigate
		//$token = $simple->className;
		//$normalized_token = strtolower($token);

		// if(isset($this->_microformats->microformats->$normalized_token)){
			// $this->_semantics['microformats'][$normalized_token][] = $line_number;
			// //showMe($target['scan_path'] . ' @ ' . $line_number . ': using microformats token ' . $normalized_token);
			// return;
		// }

		// if(preg_match('/^(?:[a-z]{1,2})-.*/i', $token)){
			// if(preg_match('/^(?:h|e|u|dt|p)-.*/i', $token)){
				// //showMe($target['scan_path'] . ' @ ' . $line_number . ': using microformats2 prefixing ' . $normalized_token);
				// $this->_microformats2Semantics($token, $normalized_token, $target, $line_number, $parameter);
				// return;
			// }

			// $single_or_double_letter_prefix = substr($normalized_token, 0, strpos($normalized_token, '-'));

			// //showMe("Speculative mf2 token format: \"" . $single_or_double_letter_prefix . "-*\" matching \"$token\" used in {$target['scan_path']} @ line $line_number");

			// if(2 == strlen($single_or_double_letter_prefix)){
				// $this->_semantics['microformats2']['prefix-format']['double-letter'][$single_or_double_letter_prefix][] = $line_number;
				// return;
			// }

			// $this->_semantics['microformats2']['prefix-format']['single-letter'][$single_or_double_letter_prefix][] = $line_number;
		// }
	}

	/**
	 * Act on Id tokens found in the selector AST
	 *
	 * @param object  $simple      The Id token.
	 * @param integer $line_number The line number of the selector.
	 *
	 * @return void
	 *
	 * @SuppressWarnings(PHPMD)
	 */
	private function _simpleSelectorId(object $simple, int $line_number)
	{
		// Not good to use microformats vocab in IDs either? (warn)
		// showMe('#' . $simple->id);
	}

	/**
	 * Act on attribute tokens found in the selector AST
	 *
	 * @param object  $simple      The attribute token.
	 * @param integer $line_number The line number of the selector.
	 *
	 * @return void
	 *
	 * @SuppressWarnings(PHPMD)
	 */
	private function _simpleSelectorAttribute(object $simple, int $line_number)
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
	 * @param object  $simple      The pseudo-class token.
	 * @param integer $line_number The line number of the selector.
	 *
	 * @return void
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function _simpleSelectorPseudoClass(object $simple, int $line_number)
	{
		//showMe(':' . $simple->name);
		if(null === $simple->parameter){
			return;
		}

		//$this->_selectorASTSimple($simple->parameter, $line_number, true);
		$this->_simple($simple->parameter, $line_number);
	}

	/**
	 * Act on pseudo-element tokens found in the selector AST
	 *
	 * @param object  $simple      The pseudo-element token.
	 * @param integer $line_number The line number of the selector.
	 *
	 * @return void
	 *
	 * @SuppressWarnings(PHPMD)
	 */
	private function _simpleSelectorPseudoElement(object $simple, int $line_number)
	{
		//showMe("::$simple->name");
	}

	/**
	 * Act on universal tokens found in the selector AST
	 *
	 * @param object  $simple      The universal token.
	 * @param integer $line_number The line number of the selector.
	 *
	 * @return void
	 *
	 * @SuppressWarnings(PHPMD)
	 */
	private function _simpleSelectorUniversal(object $simple, int $line_number)
	{
		// showMe('*');
	}

	/**
	 * Provide the details of syntax notices, warnings and errors
	 *
	 * @return mixed[] The syntax details.
	 */
	public function getSyntax() : array
	{
		return $this->_syntax;
	}

	/**
	 * Provide the details of semantics compatibility issues
	 *
	 * @return mixed[] The semantics details.
	 */
	public function getSemantics() : array
	{
		return $this->_semantics;
	}

}
