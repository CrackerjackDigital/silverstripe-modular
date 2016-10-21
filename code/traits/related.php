<?php
namespace Modular;
/**
 * Tools for dealing with relationships in SilverStripe
 *
 * @package Modular
 */
trait related {
	/**
	 * @return \Config_ForClass
	 */
	abstract public function config();
	
	/**
	 * Check if a relationship is on the exhibiting class
	 * @param $relationshipName
	 * @return bool
	 */
	public function hasRelationship($relationshipName) {
		return in_array(
			$relationshipName,
			array_merge(
				$this->config()->get('has_one') ?: [],
				$this->config()->get('has_many') ?: [],
				$this->config()->get('many_many') ?: [],
				$this->config()->get('belongs_many_many') ?: []
			)
		);
	}
}