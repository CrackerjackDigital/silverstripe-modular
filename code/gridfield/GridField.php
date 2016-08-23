<?php
namespace Modular\GridField;

use Modular\Fields\Field;

class GridField extends Field {
	const RelationshipName    = '';
	const RelatedClassName    = '';
	const GridFieldConfigName = 'Modular\GridField\HasManyGridFieldConfig';

	private static $cms_tab_name = '';

	private static $sortable = true;

	/**
	 * If model is saved then a gridfield, otherwise a 'save master first' hint.
	 *
	 * @return array
	 */
	public function cmsFields() {
		return $this()->isInDB()
			? [$this->gridField()]
			: [$this->saveMasterHint()];
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

		if ($this()->isInDB()) {
			// only add if this record is already saved
			$config->addComponent(
				new \GridFieldOrderableRows(static::GridFieldOrderableRowsFieldName)
			);
		}

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