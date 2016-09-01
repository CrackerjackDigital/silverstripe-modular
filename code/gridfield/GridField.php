<?php
namespace Modular\GridField;

use Modular\Fields\Field;
use Modular\GridField\GridFieldOrderableRows;

class GridField extends Field {
	const ShowAsGridField = 'grid';
	const RelationshipName    = '';
	const RelatedClassName    = '';
	const GridFieldConfigName = 'Modular\GridField\GridFieldConfig';

	const GridFieldOrderableRowsFieldName = GridFieldOrderableRows::SortFieldName;

	// wether to show the field as a GridField or a TagField
	private static $show_as = self::ShowAsGridField;

	// can related models be in an order so a GridFieldOrderableRows component is added?
	private static $sortable = true;

	// allow new related models to be created
	private static $allow_add_new = true;

	// show autocomplete existing filter
	private static $autocomplete = true;

	/**
	 * Return a gridfield
	 *
	 * @return array
	 */
	public function cmsFields() {
		return $this->gridFields();
	}

	/**
	 * Return field(s) to show a gridfield in the CMS, or a 'please save...' prompt if the model hasn't been saved
	 *
	 * @return array
	 */
	protected function gridFields() {
		return [
			$this()->isInDB()
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

	public static function related_class_name() {
		return static::RelatedClassName;
	}

	public static function relationship_name($fieldName = '') {
		return static::RelationshipName . ($fieldName ? ".$fieldName" : '');
	}

	/**
	 * Return a GridField configured for editing attached MediaModels. If the master record is in the database
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

		/** @var GridField $gridField */
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
