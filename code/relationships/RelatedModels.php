<?php
namespace Modular\Relationships;

use Modular\Fields\Field;
use Modular\Model;

class RelatedModels extends Field {
	const ShowAsGridField = 'grid';
	const ShowAsTagsField = 'tags';
	const RelationshipName    = '';
	const RelatedClassName    = '';
	const GridFieldConfigName = 'Modular\GridField\GridFieldConfig';

	const GridFieldOrderableRowsFieldName = GridFieldOrderableRows::SortFieldName;

	// wether to show the field as a RelatedModels or a TagField
	private static $show_as = self::ShowAsGridField;

	// can related models be in an order so a GridFieldOrderableRows component is added?
	private static $sortable = true;

	// allow new related models to be created
	private static $allow_add_new = true;

	// show autocomplete existing filter
	private static $autocomplete = true;
	
	private static $can_create_tags = false;
	
	private static $multiple_select = true;
	
	
	/**
	 * Customise if shows as a RelatedModels or a TagField depending on config.show_as
	 * @return array
	 */
	public function cmsFields() {
		if ($this->config()->get('show_as') == self::ShowAsTagsField) {
			$fields = $this->tagFields();
		} else {
			$fields = $this->gridFields();
		}
		return $fields;
	}
	
	
	
	/**
	 * Returns a field array using a tag field which can be used in derived classes instead of a RelatedModels which is the default returned by cmsFields().
	 * @return array
	 */
	protected function tagFields() {
		
		return [
			static::RelationshipName =>  $this()->isInDB()
		        ? $this->tagField()
				: $this->saveMasterHint()
		];
	}
	
	/**
	 * Return field(s) to show a gridfield in the CMS, or a 'please save...' prompt if the model hasn't been saved
	 *
	 * @return array
	 */
	protected function gridFields() {
		return [
			static::RelationshipName => $this()->isInDB()
				? $this->gridField()
				: $this->saveMasterHint(),
		];
	}

	public static function sortable() {
		return static::config()->get('sortable');
	}

	public static function field_name($append = '') {
		return static::RelationshipName . $append;
	}

	/**
	 * Returns the related class name optionally appended by '.fieldName', so e.g. when used as a filter in a relationship you will get full
	 * namespaced class for the relationship column.
	 *
	 * @param string $fieldName
	 * @return string
	 */
	public static function related_class_name($fieldName = '') {
		return static::RelatedClassName . ($fieldName ? ".$fieldName" : '');
	}

	public static function relationship_name($fieldName = '') {
		return static::RelationshipName . ($fieldName ? ".$fieldName" : '');
	}

	protected function tagField() {
		return (new \TagField(
			static::relationship_name(),
			null,
			\DataObject::get(static::RelatedClassName)
		))->setIsMultiple(
			(bool) $this->config()->get('multiple_select')
		)->setCanCreate(
			(bool) $this->config()->get('can_create_tags')
		);
	}
	
	/**
	 * Return a RelatedModels configured for editing attached MediaModels. If the master record is in the database
	 * then also add GridFieldOrderableRows (otherwise complaint re UnsavedRelationList not being a DataList happens).
	 *
	 * @param string|null $relationshipName
	 * @param string|null $configClassName name of grid field configuration class otherwise one is manufactured
	 * @return \GridField
	 */
	protected function gridField($relationshipName = null, $configClassName = null) {
		$relationshipName = $relationshipName
			?: static::RelationshipName;

		$config = $this->gridFieldConfig($relationshipName, $configClassName);

		/** @var RelatedModels $gridField */
		$gridField = \GridField::create(
			$relationshipName,
			$relationshipName,
			$this->owner->$relationshipName(),
			$config
		);

		return $gridField;
	}

	/**
	 * Allow override of grid field config
	 *
	 * @param $relationshipName
	 * @param $configClassName
	 * @return GridFieldConfig
	 */
	protected function gridFieldConfig($relationshipName, $configClassName) {
		$configClassName = $configClassName
			?: static::GridFieldConfigName
				?: get_class($this) . 'GridFieldConfig';

		/** @var GridFieldConfig $config */
		$config = $configClassName::create();
		$config->setSearchPlaceholder(

			singleton(static::RelatedClassName)->fieldDecoration(
				static::RelationshipName,
				'SearchPlaceholder',
				"Link existing {plural} by Title"
			)
		);

		if ($this()->isInDB()) {
			// only add if this record is already saved
			$config->addComponent(
				new GridFieldOrderableRows(static::GridFieldOrderableRowsFieldName)
			);
		}

		if (!$this->config()->get('allow_add_new')) {
			$config->removeComponentsByType(GridFieldConfig::ComponentAddNewButton);
		}
		if (!$this->config()->get('autocomplete')) {
			$config->removeComponentsByType(GridFieldConfig::ComponentAutoCompleter);
		}

		return $config;
	}

	/**
	 * When a page with blocks is published we also need to publish blocks. Blocks should also publish their 'sub' blocks.
	 */
	public function onAfterPublish() {
		/** @var Model|\Versioned $block */
		foreach ($this()->{static::RelationshipName}() as $block) {
			if ($block->hasExtension('Versioned')) {
				$block->publish('Stage', 'Live', false);
			}
		}
	}
}
