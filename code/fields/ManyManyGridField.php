<?php
namespace Modular\Fields;

use Modular\GridField\GridFieldConfig;
use Modular\Relationships\HasManyMany;
use Quaff\Controllers\Model;

use Modular\GridField\GridFieldOrderableRows;

class HasManyManyGridField extends HasManyMany {
	const GridFieldConfigName = 'Modular\GridField\HasManyManyGridFieldConfig';

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

		/** @var HasManyManyGridField $gridField */
		$gridField = \GridField::create(
			$relationshipName,
			$relationshipName,
			$this->owner->$relationshipName(),
			$config
		);

		if ($this()->isInDB()) {
			// only add if this record is already saved
			$config->addComponent(
				new GridFieldOrderableRows(static::SortFieldName)
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

		/** @var GridFieldConfig $config */
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

}