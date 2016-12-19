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
		// could get a null gridfield so filter it out
		return array_filter(
			$this()->isInDB()
			? [$this->gridField()]
			: [$this->saveMasterHint()]
		);
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

		$gridField = null;

		if ($this()->hasMethod($relationshipName)) {
			$list = $this()->$relationshipName();
			/** @var \GridField $gridField */
			$gridField = \GridField::create(
				$relationshipName,
				$relationshipName,
				$list,
				$config
			);

			if ($this()->isInDB()) {
				// only add if this record is already saved
				$config->addComponent(
					new GridFieldOrderableRows(static::GridFieldOrderableRowsFieldName)
				);
			}
			return $gridField;

		}
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
			?: static::gridfield_config_class();


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