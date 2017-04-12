<?php
namespace Modular\Traits;

use Modular\Fields\ModelTag;
use RelationList;

/**
 * Override SilverStripe duplicate methods with those that allow a 'deep copy' of related models to be made in a configurable manner.
 *
 * @package Modular\Traits
 */
trait duplication {

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
		unset( $fieldData['ID'] );
		unset( $fieldData[ ModelTag::SingleFieldName ] );

		// create a copy of this model with unique field requirements resolved
		$clone = $className::create( $fieldData, false, $this->model );

		$clone->invokeWithExtensions( 'onBeforeDuplicate', $this, $doWrite );
		if ( $doWrite ) {
			$clone->write();
			// copy relationships which exist on this model to the newly related clone,
			// creating copies of each related model as we go
			$this->duplicateManyManyRelations( $this, $clone );
		}
		$clone->invokeWithExtensions( 'onAfterDuplicate', $this, $doWrite );

		return $clone;
	}

	/**
	 * Copies the many_many and belongs_many_many relations from one object to another instance of the name of object
	 * The destinationObject must be written to the database already and have an ID. Writing is performed
	 * automatically when adding the new relations.
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

		//duplicate complex relations
		// DO NOT copy has_many relations, because copying the relation would result in us changing the has_one
		// relation on the other side of this relation to point at the copy and no longer the original (being a
		// has_one, it can only point at one thing at a time). So, all relations except has_many can and are copied
		if ( $sourceObject->hasOne() ) {
			foreach ( $sourceObject->hasOne() as $name => $type ) {
				$this->duplicateRelations( $sourceObject, $destinationObject, $name );
			}
		}
		if ( $sourceObject->manyMany() ) {
			foreach ( $sourceObject->manyMany() as $name => $type ) {
				//many_many include belongs_many_many
				$this->duplicateRelations( $sourceObject, $destinationObject, $name );
			}
		}

		return $destinationObject;
	}

	/**
	 * Helper function to duplicate relations from one object to another. We override the default SilverStripe functionality so
	 * we create a new version of the foreign models and relationship to the new foreign model rather than just create a new relationship
	 * to the existing model.
	 *
	 * @param \DataObject $sourceObject      the source object to duplicate from
	 * @param \DataObject $destinationObject the destination object to populate with the duplicated relations
	 * @param string      $relationshipName  the name of the relation to duplicate (e.g. members)
	 */
	private function duplicateRelations( $sourceObject, $destinationObject, $relationshipName ) {
		$relations = $sourceObject->$relationshipName();
		if ( $relations ) {
			if ( $relations instanceOf RelationList ) {   //many-to-something related
				if ( $relations->Count() > 0 ) {
					// with more than one thing it is related to
					foreach ( $relations as $related ) {

						if ( $this->shouldDeepCopyClass( $related->class ) ) {
							// create a 'deep copy' of the related model
							$related = $related->duplicate( true );
						}
						// relate either the copy or the existing related class
						$destinationObject->$relationshipName()->add( $related );
					}
				}
			} else {
				// one-to-one related, we need to create a copy of the 'to' model and add that
				/** @var \DataObject $related */
				if ( $related = $destinationObject->{$relationshipName}() ) {
					if ( $related->exists() ) {

						if ( $this->shouldDeepCopyClass( $related->class ) ) {
							$related = $related->duplicate( true );
						}
						// relate either the copy or the existing related class
						$destinationObject->{"{$relationshipName}ID"} = $related->ID;
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
	protected function shouldDeepCopyClass( $relatedClassName ) {
		// don't duplicate these blocks
		$skipDuplicateClasses = $this->config()->get( 'skip_duplicate_related_classes' ) ?: [];
		foreach ( $skipDuplicateClasses as $skipClassName ) {
			if ( is_a( $relatedClassName, $skipClassName, true ) ) {
				return false;
			}
		}

		return true;
	}

}