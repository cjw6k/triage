<?php
/**
 * The SemanticsTrait trait is herein defined.
 *
 * @copyright (C) 2018 by the contributors
 *
 * LICENSE: See the /LICENSE.md file for details (MIT)
 *
 * @package	Triage
 * @author	Christopher James Willcock <cjwillcock@ieee.org>
 * @link	https://triage.cjwillcock.ca/
 */

declare(strict_types = 1);

namespace Triage\Triage\Reporter\Semantics;

trait SemanticsTrait
{

	/**
	 * The set of troubles for this group
	 *
	 * @var mixed[]
	 */
	private $_troubles = array();

	/**
	 * The count of troubles in this group
	 *
	 * @var integer
	 */
	private $_count = null;

	/**
	 * Capture the set of troubles for this group
	 *
	 * @param mixed[] $troubles The set of troubles.
	 */
	public function __construct(array $troubles)
	{
		$this->_troubles = $troubles;
	}

	/**
	 * Provide the count of troubles within the group
	 *
	 * Caches the count the first time it is called and provides the cached
	 * value for the next calls.
	 *
	 * @return integer The count of troubles.
	 */
	public function getCount() : int
	{
		if(null !== $this->_count){
			return $this->_count;
		}

		$trouble_count = 0;
		if(!empty($this->_troubles)){
			foreach($this->_troubles as $troubles){
				$trouble_count += count($troubles);
			}
		}

		return $trouble_count;
	}

}
