<?php
/**
 * The Cleaner class is herein defined.
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

namespace Triage\Triage\Analyzer\Css\Ast;

/**
 * Cleaner attempts to correct syntax issues and \PhpCss limitations to fully scan a CSS file
 */
class Cleaner
{

	/**
	 * The current CSS selector under examination
	 *
	 * @var string
	 */
	private $_selector = "";

	/**
	 * An account of any changes made to the CSS selector to cause it to parse
	 *
	 * @var mixed[]
	 */
	private $_replacements = array();

	/**
	 * Attempt to clean syntax problems with the selector
	 *
	 * @param string $selector The CSS selector that does not currently parse.
	 *
	 * @return integer The number of replacements made in the selector.
	 */
	public function clean(string $selector) : int
	{
		$this->_reset();

		$this->_selector = preg_replace_callback_array(
			array(
				// pseudo-class syntax used for a pseudo-element
				'/(?<!:)(:(after|backdrop|before|cue|first-letter|first-line|grammar-error|marker|placeholder|selection|slotted|spelling-error))/' => function($match) use ($selector){
					$this->_pseudoElementConfusion($match, $selector);
				},

				// pseudo-element syntax used for a pseduo-class
				'/::(active|any|any-link|checked|default|defined|dir\(.*\)|disabled|empty|enabled|first|first-child|first-of-type|fullscreen|focus|host|host\(.*\)|host-context\(.*\)|hover|indeterminate|in-range|invalid|lang\(.*\)|last-child|last-of-type|left|link|not\(.*\)|nth-child\(.*\)|nth-last-child\(.*\)|nath-last-of-type\(.*\)|nth-of-type\(.*\)|only-child|only-of-type|optional|out-of-range|read-only|read-write|required|right|root|scope|target|valid|visited)/' => function($match) use ($selector){
					$this->_pseudoClassConfusion($match, $selector);
				},

				// nth-*() parameter of 0, which is syntactically valid but useless and not recognized in \PhpCss (positions start at 1)
				'/:nth\-(child|of\-type|last\-child)\(0\)/' => function($match) use ($selector){
					$this->_nthChildZero($match, $selector);
				},

				// invalid characters appearing in selectors
				//   <200b> zero width space
				//   <200c> zero width non-joiner
				//   <200d> zero width joiner
				'/[\x{200B}-\x{200D}]/u' => function($match) use ($selector){
					$this->_badCharacters($match, $selector);
				},

				// quoted a classname in the argument to a :not, as if it was a string literal
				"/:not\('\.(.+)'\)/" => function ($match) use ($selector){
					$this->_quotedClassname($match, $selector);
				},

				// used a class or Id instead of a pseudo-class position
				"/:nth\-(last\-)?(child|of\-type)\((\.|#).*\)/" => function ($match) use ($selector){
					$this->_badPseudoClassPosition($match, $selector);
				},

				// unrecognized vendor extension
				"/:?(:-ms-|:-webkit-|:-moz-|:-o-)[0-9a-z\-]*/" => function($match) use ($selector){
					$this->_vendorPrefix($match, $selector);
				},

				// Empty :not()
				'/:not\(\)/' => function($match) use ($selector){
					$this->_emptyNot($match, $selector);
				},

				// Experimental pseudo-elements
				'/::(backdrop|marker|placeholder|spelling-error|grammar-error)[^-0-9a-zA-Z]*$/' => function($match) use ($selector){
					$this->_experimentalPseudoElement($match, $selector);
				},

				// Unsupported-in-PhpCss pseudo-elements
				'/::(cue|selection|slotted)[^-0-9a-zA-Z]*$/' => function(){
					return '';
				},

				// Experimental pseudo-classes
				'/:(any-link|dir\(.*\)|fullscreen|host\(.*\)|host-context\(.*\))[^-0-9a-zA-Z]*$/' => function($match) use ($selector){
					$this->_experimentalPseudoClass($match, $selector);
				},

				// Unsupported-in-PhpCss pseudo-classes
				'/:(default|defined|first|host|in-range|indeterminate|invalid|left|optional|out-of-range|read-only|read-write|required|right|scope|valid)[^-0-9a-zA-Z]*$/' => function(){
					return '';
				},
			),
			$selector,
			-1,
			$replacement_count
		);
		
		return $replacement_count;
	}

	/**
	 * Reset the cleaner to clean a new selector
	 *
	 * @return void
	 */
	private function _reset()
	{
		$this->_replacements = array();
	}

	/**
	 * Correct syntax where a pseudo-element has pseudo-class syntax
	 *
	 * @param string[] $match    The section of selector that has the trouble.
	 * @param string   $selector The selector before applying this replacement.
	 *
	 * @return string The replacement string for that section of the selector.
	 */
	private function _pseudoElementConfusion(array $match, string $selector) : string
	{
		return ":{$match[0]}";
		// return $this->_recordReplacement(
			// 'warnings/pseudo-element-confusion',
			// $selector,
			// $match[0],
			// ":{$match[0]}"
		// );
	}
	
	/**
	 * Correct syntax where a pseudo-class has pseudo-element syntax
	 *
	 * @param string[] $match    The section of selector that has the trouble.
	 * @param string   $selector The selector before applying this replacement.
	 *
	 * @return string The replacement string for that section of the selector.
	 */
	private function _pseudoClassConfusion(array $match, string $selector) : string
	{
		return ":{$match[0]}";
		// return $this->_recordReplacement(
			// 'warnings/pseudo-class-confusion',
			// $selector,
			// $match[0],
			// ":{$match[0]}"
		// );
	}

	/**
	 * Correct syntax where an nth-child of zero is the trouble
	 *
	 * @param string[] $match    The section of selector that has the trouble.
	 * @param string   $selector The selector before applying this replacement.
	 *
	 * @return string The replacement string for that section of the selector.
	 */
	private function _nthChildZero(array $match, string $selector) : string
	{
		return $this->_recordReplacement(
			'warnings/ordinality',
			$selector,
			$match[0],
			":nth-{$match[1]}(0n)"
		);
	}

	/**
	 * Correct syntax where invalid characters are the trouble
	 *
	 * @param string[] $match    The section of selector that has the trouble.
	 * @param string   $selector The selector before applying this replacement.
	 *
	 * @return string The replacement string for that section of the selector.
	 */
	private function _badCharacters(array $match, string $selector) : string
	{
		return $this->_recordReplacement(
			'warnings/bad-characters',
			$selector,
			$match[0],
			''
		);
	}

	/**
	 * Correct syntax where a quoted classname is the trouble
	 *
	 * @param string[] $match    The section of selector that has the trouble.
	 * @param string   $selector The selector before applying this replacement.
	 *
	 * @return string The replacement string for that section of the selector.
	 */
	private function _quotedClassname(array $match, string $selector) : string
	{
		return $this->_recordReplacement(
			'warnings/quote-all-the-things',
			$selector,
			$match[0],
			":not(.{$match[1]})"
		);
	}

	/**
	 * Correct syntax where an invalid pseudo-class position is the trouble
	 *
	 * @param string[] $match    The section of selector that has the trouble.
	 * @param string   $selector The selector before applying this replacement.
	 *
	 * @return string The replacement string for that section of the selector.
	 */
	private function _badPseudoClassPosition(array $match, string $selector) : string
	{
		return $this->_recordReplacement(
			'warnings/bad-pseudo-class-position',
			$selector,
			$match[0],
			":nth-{$match[1]}(0n)"
		);
	}

	/**
	 * Correct syntax where an unrecognized vendor extension is the trouble
	 *
	 * @param string[] $match    The section of selector that has the trouble.
	 * @param string   $selector The selector before applying this replacement.
	 *
	 * @return string The replacement string for that section of the selector.
	 */
	private function _vendorPrefix(array $match, string $selector) : string
	{
		return $this->_recordReplacement(
			'notices/unrecognized-vendor-extension',
			$selector,
			$match[0],
			''
		);
	}

	/**
	 * Correct syntax where an empty :not() is the trouble
	 *
	 * @param string[] $match    The section of selector that has the trouble.
	 * @param string   $selector The selector before applying this replacement.
	 *
	 * @return string The replacement string for that section of the selector.
	 */
	private function _emptyNot(array $match, string $selector) : string
	{
		return $this->_recordReplacement(
			'warnings/empty-not',
			$selector,
			$match[0],
			''
		);
	}

	/**
	 * Alter syntax to parse where an experimental pseudo-element is the trouble
	 *
	 * @param string[] $match    The section of selector that has the trouble.
	 * @param string   $selector The selector before applying this replacement.
	 *
	 * @return string The replacement string for that section of the selector.
	 */
	private function _experimentalPseudoElement(array $match, string $selector) : string
	{
		return $this->_recordReplacement(
			'notices/experimental-pseudo-element',
			$selector,
			$match[0],
			''
		);
	}

	/**
	 * Alter syntax to parse where an experimental pseudo-class is the trouble
	 *
	 * @param string[] $match    The section of selector that has the trouble.
	 * @param string   $selector The selector before applying this replacement.
	 *
	 * @return string The replacement string for that section of the selector.
	 */
	private function _experimentalPseudoClass(array $match, string $selector) : string
	{
		return $this->_recordReplacement(
			'notices/experimental-pseudo-class',
			$selector,
			$match[0],
			''
		);
	}

	/**
	 * Record the replacement of a section of a selector, for later reporting
	 *
	 * @param string $type     The type of replacement.
	 * @param string $selector The unmodified selector.
	 * @param string $match	   The section of the selector that has the trouble.
	 * @param string $after	   The new content to used for the matched section.
	 *
	 * @return string The replacement string for that section of the selector
	 */
	private function _recordReplacement(string $type, string $selector, string $match, string $after) : string
	{
		$this->_replacements[$type][] = array(
			'before' => $selector,
			'match' => $match,
			'after' => $after,
		);

		return $after;
	}

	/**
	 * Provide the selector after any replacements
	 *
	 * @return string The selector.
	 */
	public function getCleanedSelector() : string
	{
		return trim($this->_selector);
	}

	/**
	 * Provide the details of each modification made to the selector
	 *
	 * @return mixed[] The replacements made to the selector.
	 */
	public function getReplacements() : array
	{
		return $this->_replacements;
	}

}
