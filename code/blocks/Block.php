<?php

namespace Modular\Blocks;

use DataObject;
use GridListBlock;
use HiddenField;
use Modular\Application;
use Modular\Interfaces\LinkType;
use Modular\Models\GridListFilter;
use Modular\Models\Tag;
use RelationList;

/**
 * Class which represents a block which can be added to an Article, of types ( in display order ). The types in the grid dropdown are determined by
 * subclasses of this class, so there is no need e.g. for a 'BlockType' lookup or relationship.
 * 'Text',
 * 'Video',
 * 'Audio',
 * 'Images (gallery)',
 * 'Image (full width)',
 * 'Footnotes',
 * 'Links',
 * 'Download',
 * 'Pull Quote'
 *
 * @method \DataList Pages()
 */
class Block extends \Modular\VersionedModel implements LinkType {
	private static $template = '';

	private static $summary_fields = [
		'BlockType'  => 'Block Type',
		'BlockZones' => 'Zone(s)',
	];

	private static $link_type = '';

	private static $prefix_duplicated_fields = [
		'Title' => 'Copy of ',
	];

	private static $skip_duplicate_related_classes = [
		'Page',
	    'MasterFilter',
	    'Modular\Models\Tag',
		'Modular\Models\GridListFilter'
	];

	/**
	 * Create a duplicate of this model and it's db field values if we specify 'write' then we will also duplicate any related models
	 * and create relationships of the same respective types (many_many, belongs_many_many and has_one) from the new model to the new related models.
	 *
	 * @param bool $doWrite Perform a write() operation (and also copy many_many relations )
	 *
	 * @return DataObject The newly created duplicate model.
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
		// create a copy of this model with unique field requirements resolved
		$clone     = $className::create( $fieldData, false, $this->model );
		$clone->ID = 0;

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
	 * @return DataObject with the new many_many relations copied in
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
			foreach ( $sourceObject->hasOne() as $name => $foreignModelClass ) {
				$this->duplicateRelations( $sourceObject, $destinationObject, $name );
			}
		}
		// for blocks we don't want to copy belongs_many_many
		if ( $manyMany = $sourceObject->config()->get( 'many_many' ) ) {
			foreach ( $manyMany as $name => $foreignModelClass ) {
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

	/**
	 * When we do an add new multi class we need to tell it what the ClassName is.
	 *
	 * @return \FieldList
	 */
	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->push( new HiddenField( 'ClassName', '', get_class( $this ) ) );

		return $fields;
	}

	public function BlockType() {
		return $this->i18n_singular_name();
	}

	/**
	 * Return a csv of zones for this block class.
	 *
	 * @return mixed
	 */
	public function BlockZones() {
		$zones         = [];
		$blocksForZone = \Config::inst()->get( 'Page', 'blocks_for_zone' );

		foreach ( $blocksForZone as $zone => $zoneBlocks ) {
			foreach ( $zoneBlocks as $blockClass ) {
				if ( $blockClass == $this->ClassName ) {
					$zones[] = $zone;
				}
			}
		}

		return implode( ', ', $zones );
	}

	/**
	 * Returns:
	 *  -   configured config.link_type for this block
	 *
	 * or if not configured
	 *
	 *  -   terminal part of the class name of this block without namespace and without 'Block' suffix
	 *
	 * # VideoBlock => 'Video'
	 * # Modular\Blocks\CallToAction => 'CallToAction'
	 *
	 * @return string
	 */
	public function LinkType() {
		if ( ! $linkType = $this->config()->get( 'link_type' ) ) {
			$linkType = current( array_reverse( explode( '\\', static::block_class() ) ) );
			$linkType = ( substr( $linkType, - 5 ) == 'Block' )
				? substr( $linkType, 0, - 5 )
				: $linkType;
		}

		return $linkType;
	}

	/**
	 * Return text to show in a link to this block (or more likely a link this block contains, such as a File via the HasLinks interface).
	 *
	 * @return mixed
	 */
	public function LinkText() {
		$blockClass = get_class( $this );

		return _t( "$blockClass.LinkText", 'MORE' );
	}

	/**
	 * @return string
	 */
	public static function block_class() {
		return get_called_class();
	}

	public function DisplayInSidebar() {
		return false;
	}

	public function DisplayInContent() {
		return true;
	}

	/**
	 * Ok so this makes Blocks a 'Model-View' but we already have that via ViewableData so run with it.
	 *
	 * @return \HTMLText
	 */
	public function forTemplate() {
		return $this->renderWith( $this->templates() );
	}

	protected function template() {
		return $this->config()->get( 'template' ) ?: $this->class;
	}

	protected function templates() {
		return [ $this->template() ];
	}

	/**
	 * Return the current page from Director.
	 *
	 * @return \Page
	 */
	public function CurrentPage() {
		/** @var \Page $parent */
		return Application::get_current_page();
	}

	/**
	 * Return current pages ClassName.
	 *
	 * @return string
	 */
	public function PageClassName() {
		return Application::get_current_page()->ClassName;
	}

}
