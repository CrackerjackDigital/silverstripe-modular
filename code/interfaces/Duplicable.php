<?php

namespace Modular\Interfaces;

interface Duplicable {
	// constants to use for specifying how a related model is duplicated
	// 'onto' the new model
	/** Don't duplicate this at all, if present then remove it */
	const DuplicateNothing = 1;

	/** Create a relationship to the model (either original or copy) */
	const DuplicateRelationship = 2;

	/** Create a duplicate of the model and a relationship to it, implies DuplicateRelationship  */
	const DuplicateDeepCopy = 6;

	/** By default we Deep Copy the foreign model */
	const DuplicateDefault = self::DuplicateDeepCopy;

	/**
	 *
	 * Return a rule for the provided model class looked up in config.relationship_duplication_rules. If entry with key of
	 * provided model class is not found then return null. Used by duplication extension to figure
	 * out what to do when duplicating relationships.
	 *
	 * @param string $forModelOrClass to lookup rule for
	 *
	 * @return int|null
	 */
	public function duplicationRule($forModelOrClass);

	/**
	 * Perform the model duplication, generally handled in duplication trait.
	 *
	 * @param bool $doWrite
	 *
	 * @return mixed
	 */
	public function duplicate( $doWrite = true );
}