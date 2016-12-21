<?php
namespace Modular\Fields;

use Modular\GridField\GridFieldConfig;
use Modular\GridField\GridFieldOrderableRows;
use Modular\Model;

/**
 * A field that manages relationships between the extended model and other models. Can show as a GridField or a TagField
 * depending on the config.show_as setting.
 *
 * @package Modular\Fields
 */
abstract class Relationship extends Field {
	const ShowAsGridField     = 'grid';
	const ShowAsTagsField     = 'tags';
	const RelationshipName    = '';
	const RelatedClassName    = '';
	const GridFieldConfigName = 'Modular\GridField\GridFieldConfig';

	const GridFieldOrderableRowsFieldName = GridFieldOrderableRows::SortFieldName;

	// wether to show the field as a GridField or a TagField
	private static $show_as = self::ShowAsGridField;

	// can related models be in an order so a GridFieldOrderableRows component is added?
	private static $allow_sorting = true;

	// allow new related models to be created either via 'add' button or by adding a tag
	private static $allow_create = true;

	// allow multiple relationships to be created (really only for tag fields)
	private static $allow_multiple = true;

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

	protected function availableTags() {
		$tagClassName = static::RelatedClassName;
		return $tagClassName::get()->sort('Title');
	}

	public static function allow_sorting() {
		return static::config()->get('allow_sorting');
	}

	public static function field_name($suffix = '') {
		return static::RelationshipName . $suffix;
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

	/**
	 * Returns a field array using a tag field which can be used in derived classes instead of a GridField which is the default returned by cmsFields().
	 *
	 * @return array
	 */
	protected function tagFields() {
		// could get a null tag field so filter it out
		return [ $this->tagField() ?: $this->saveMasterHint() ];
	}

	/**
	 * Return field(s) to show a gridfield in the CMS, or a 'please save...' prompt if the model hasn't been saved
	 *
	 * @return array
	 */
	protected function gridFields() {
		// could get a null gridfield so filter it out
		return [ $this->gridField() ?: $this->saveMasterHint() ];
	}

	protected function tagField() {
		if ($this()->isInDB()) {
			$multipleSelect = (bool) $this->config()->get('allow_multiple');
			$canCreate = (bool) $this->config()->get('allow_create');

			return \TagField::create(
					static::relationship_name(),
					null,
					$this->availableTags()
				)->setIsMultiple(
					$multipleSelect
				)->setCanCreate(
					$canCreate
				);
		}
	}

	/**
	 * If owner is in database then return a GridField configured for editing attached Models.
	 *
	 * @param string|null $relationshipName
	 * @param string|null $configClassName name of grid field configuration class otherwise one is manufactured
	 * @return \GridField
	 */
	protected function gridField($relationshipName = null, $configClassName = null) {
		if ($this()->isInDB()) {
			// relationshipName and configClassName if empty will be updated according to what GridFieldConfig determins
			$config = $this->gridFieldConfig($relationshipName, $configClassName);

			if ($this()->hasMethod($relationshipName)) {
				// we need to guard this for when changing page types in CMS
				$list = $this()->$relationshipName();
				/** @var \GridField $gridField */

				return \GridField::create(
					$relationshipName,
					$relationshipName,
					$list,
					$config
				);
			}
		}
	}

	/**
	 * Returns a configured GridFieldConfig based on config.gridfield_config_class.
	 *
	 * @param string $relationshipName if not supplied then static.RelationshipName via relationship_name() and is updated
	 * @param string $configClassName  if not supplied then static.GridFieldConfigName or one is guessed, or base is used and value is updated
	 * @return GridFieldConfig
	 */
	protected function gridFieldConfig(&$relationshipName = '', &$configClassName = '') {
		$relationshipName = $relationshipName
			?: static::relationship_name();

		$configClassName = $configClassName
			?: static::gridfield_config_class();

		/** @var GridFieldConfig $config */
		$config = $configClassName::create();
		$config->setSearchPlaceholder(

			singleton(static::RelatedClassName)->fieldDecoration(
				$relationshipName,
				'SearchPlaceholder',
				"Link existing {plural} by Title"
			)
		);

		if ($this()->isInDB()) {
			// only add if this record is already saved otherwise can get an error.
			$config->addComponent(
				new GridFieldOrderableRows(static::GridFieldOrderableRowsFieldName)
			);
		}
		// we can override settings in GridFieldConfig here
		if (!$this->config()->get('allow_create')) {
			$config->removeComponentsByType(GridFieldConfig::ComponentAddNewButton);
		}
		// we can override settings in GridFieldConfig here
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

	/**
	 * Returns configured or manufactured class name
	 * falling back to 'Modular\GridField\GridFieldConfig' if class doesn't exist.
	 *
	 * @return string
	 */
	protected static function gridfield_config_class() {
		$className = static::GridFieldConfigName;

		if (!\ClassInfo::exists($className)) {
			$className = GridFieldConfig::class_name();
		}
		return $className;
	}
}
