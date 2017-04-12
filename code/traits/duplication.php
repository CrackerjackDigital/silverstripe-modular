<?php

namespace Modular\Traits;

use Modular\Extensions\Views\AddDefaultBlocks;
use Modular\Fields\ModelTag;
use Modular\Interfaces\Duplicable;
use RelationList;

/**
 * Override SilverStripe duplicate methods with those that allow a 'deep copy' of related models to be made in a configurable manner.
 *
 * @package Modular\Traits
 */
trait duplication {

	/**
	 * Return a rule which governs how a related model class is duplicated from lookup of class name in
	 * config.relationship_duplication_rules using 'is_a' to match key to class name, or if not found then
	 * config.default_duplication_rule if set otherwise
	 * Duplicable::DuplicateDefault is chosen
	 *
	 *
	 * @param string $forModelOrClass to lookup rule for using is_a test
	 *
	 * @return int|null return the rule (one of the Duplicable::DuplicateABC constants) or null if none set/found
	 */
	public function duplicationRule( $forModelOrClass ) {
		$modelClass = is_object( $forModelOrClass )
			? get_class( $forModelOrClass )
			: $forModelOrClass;

		$rules = $this->config()->get( 'relationship_duplication_rules' ) ?: [];
		foreach ( $rules as $className => $rule ) {
			if ( is_a( $modelClass, $className, true ) ) {
				return $rule;
			}
		}

		return $this->config()->get( 'default_relationship_duplication_rule' ) ?: Duplicable::DuplicateDefault;
	}

	/**
	 * Create a duplicate of this model and it's db field values if we specify 'write' then we will also duplicate any related models
	 * and create relationships of the same respective types (many_many, belongs_many_many and has_one) from the new model to the new related models.
	 *
	 * @param bool $doWrite Perform a write() operation (and also copy many_many relations )
	 *
	 * @return \DataObject The newly created duplicate model.
	 */
	public function duplicate( $doWrite = true ) {
		$className = $this->class;
		$fieldData = $this->toMap();

		// prefix nominated fields to prevent unique field value constraints from preventing the new model being written
		foreach ( $this->config()->get( 'prefix_duplicated_fields' ) ?: [] as $fieldName => $prefixWith ) {
			if ( isset( $fieldData[ $fieldName ] ) ) {
				$fieldData[ $fieldName ] = $prefixWith . $fieldData[ $fieldName ];
			}
		}
		// sanitise fields we don't want copied
		unset( $fieldData['ID'] );
		unset( $fieldData[ ModelTag::SingleFieldName ] );
		unset( $fieldData['Created'] );
		unset( $fieldData['LastEdited'] );
		unset( $fieldData['Version'] );

		// disable default block addition
		AddDefaultBlocks::disable();

		// create a copy of this model from sanitised source models fields
		$clone = new $className( $fieldData, false, $this->model );
		$clone->ID = 0;

		$clone->invokeWithExtensions( 'onBeforeDuplicate', $this, $doWrite );
		if ( $doWrite ) {
			// we need to disable Field validation as we're going to null out fields,
			// e.g. has_one ID fields which are normally required
			\Config::inst()->update($className, 'validation_enabled', false);

			$clone->write();
			// copy relationships which exist on this model to the newly related clone,
			// creating copies of each related model as we go
			$this->duplicateManyManyRelations( $this, $clone );

			// write the clone again to save relationship changes
			$clone->write();
		}
		$clone->invokeWithExtensions( 'onAfterDuplicate', $this, $doWrite );

		return $clone;
	}

	/**
	 * Copies the has_one and many_many relations from one object to another instance of the same object
	 * The destinationObject must be written to the database already and have an ID. Writing is performed
	 * automatically when adding the new relations. At the end
	 *
	 * @param \DataObject $sourceObject      the source object to duplicate from
	 * @param \DataObject $destinationObject the destination object to populate with the duplicated relations
	 *
	 * @return \DataObject with the new many_many relations copied in
	 */
	protected function duplicateManyManyRelations( $sourceObject, $destinationObject ) {
		if ( ! $destinationObject || $destinationObject->ID < 1 ) {
			user_error( "Can't duplicate relations for an object that has not been written to the database",
				E_USER_ERROR );
		}

		if ( $hasOnes = $sourceObject->config()->get( 'has_one' ) ) {
			foreach ( $hasOnes as $name => $type ) {
				$this->duplicateRelations( $sourceObject, $destinationObject, $name );
			}
		}
		// only many_many relationships, we don't want belongs_many_many
		if ( $manyManys = $sourceObject->config()->get( 'many_many' ) ) {
			foreach ( $manyManys as $name => $type ) {
				$this->duplicateRelations( $sourceObject, $destinationObject, $name );
			}
		}

		return $destinationObject;
	}

	/**
	 * Helper function to duplicate relations from one object to another. We override the default SilverStripe functionality so
	 * we can create a deep copy of the foreign model and relationships rather than just create a relationship
	 * to the existing model.
	 *
	 * @param \DataObject $sourceObject      the source object to duplicate from
	 * @param \DataObject $destinationObject the destination object to populate with the duplicated relations
	 * @param string      $relationshipName  the name of the relation to duplicate (e.g. members)
	 */
	private function duplicateRelations( $sourceObject, $destinationObject, $relationshipName ) {
		$sourceRelation = $sourceObject->$relationshipName();
		if ( $sourceRelation ) {
			if ( ($sourceRelation instanceOf RelationList) && ($sourceRelation->Count() > 0 )) {
				/** @var \Modular\Traits\duplication $related */
				foreach ( $sourceRelation as $related ) {
					$relatedClassName = get_class( $related );

					if ( $this->shouldDuplicateRelationship( $relatedClassName ) ) {

						if ( $this->shouldDeepCopyRelatedClass( $relatedClassName) ) {
							// create a 'deep copy' of the related model
							$related = $related->duplicate( true );
						}
						// add new or existing model to relationship
						$destinationObject->$relationshipName()->add( $related );
					}
				}
			} else {
				// handle has_one relationships
				if ( ($sourceRelation instanceof \DataObject) && $sourceRelation->exists() ) {
					$relatedClassName = get_class($sourceRelation);

					// clear existing relationship to the model on the destination object
					$destinationObject->{"{$relationshipName}ID"} = null;
					if ( $this->shouldDuplicateRelationship( $relatedClassName) ) {

						if ( $this->shouldDeepCopyRelatedClass( $relatedClassName ) ) {
							// create a 'deep copy' of the related model
							$sourceRelation = $sourceRelation->duplicate( true );
						}
						// set either new or existing model as related model
						$destinationObject->{"{$relationshipName}ID"} = $sourceRelation->ID;
					}
				}
			}
		}
	}

	/**
	 * Given a class name check if it is an instance of one of our config.skip_duplicate_related_classes class names, if so return true, otherwise false.
	 *
	 * @param $relatedClassName
	 *
	 * @return bool
	 */
	protected function shouldDuplicateRelationship( $relatedClassName ) {
		$rule = $this->duplicationRule( $relatedClassName );

		return ( ( $rule & Duplicable::DuplicateRelationship ) === Duplicable::DuplicateRelationship );
	}

	/**
	 * Given a class name check if it is an instance of one of our config.skip_duplicate_related_classes class names, if so return true, otherwise false.
	 *
	 * @param $relatedClassName
	 *
	 * @return bool
	 */
	protected function shouldDeepCopyRelatedClass( $relatedClassName ) {
		$rule = $this->duplicationRule( $relatedClassName );

		return ( ( $rule & Duplicable::DuplicateDeepCopy ) === Duplicable::DuplicateDeepCopy );
	}

}