<?php
namespace Modular\Relationships;

use Modular\GridField\Configs\GridFieldConfig;
use Modular\GridField\Components\GridFieldOrderableRows;
use Modular\Model;
use Modular\Traits\reflection;

class RelatedModels extends \Modular\Field {
	use reflection;

	const ShowAsGridField     = 'grid';
	const ShowAsTagsField     = 'tags';
	const RelationshipName    = '';
	const RelatedClassName    = '';
	const RelationshipPrefix  = '';
	const GridFieldConfigName = 'Modular\GridField\GridFieldConfig';
	const Arity = null;

	const SortFieldName = GridFieldOrderableRows::SortFieldName;

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
	 * Customise if shows as a GridField or a TagField depending on config.show_as
	 *
	 * @param $mode
	 * @return array
	 */
	public function cmsFields($mode = null) {
		if ($this->config()->get('show_as') == self::ShowAsTagsField) {
			$fields = $this->tagFields();
		} else {
			$fields = $this->gridFields();
		}
		return $fields;
	}

	/**
	 * Return all related items. Optionally (for convenience more than anything) provide a relationship name to dereference otherwise this classes
	 * late static binding relationship_name() will be used.
	 *
	 * @param string $relationshipName if supplied use this relationship instead of static relationship_name
	 * @return \ArrayList|\DataList
	 */
	public function related($relationshipName = '') {
		$relationshipName = $relationshipName ?: static::relationship_name();
		return $this()->$relationshipName();
	}

	/**
	 * Return an array of IDs from the other end of this extendsions Relationship or the supplied relationship name.
	 *
	 * @param string $relationshipName
	 * @return array
	 */
	public function relatedIDs($relationshipName = '') {
		return $this->related($relationshipName)->column('ID');
	}

	/**
	 * Returns a field array using a tag field which can be used in derived classes instead of a RelatedModels which is the default returned by cmsFields().
	 *
	 * @return array
	 */
	protected function tagFields() {

		return [
			static::RelationshipName => $this()->isInDB()
				? $this->tagField()
				: $this->saveMasterHint(),
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

	/**
	 * has_one relationships need an 'ID' appended to the relationship name to make the field name
	 *
	 * @param string $suffix defaults to 'ID'
	 * @return string
	 */
	public static function related_field_name($suffix = '') {
		return static::RelationshipName . $suffix;
	}

	/**
	 * Return unadorned has_one related class name.
	 *
	 * @return string
	 */
	public static function related_class_name() {
		return static::RelatedClassName;
	}

	/**
	 * Return the name of the relationship on the extended model, e.g. 'Members' or 'SocialOrganisations'. This will be
	 * made from the Related Class Name with 's' appended or can be override by self.RelationshipName
	 *
	 * @param string $fieldName
	 * @return string
	 */
	public static function relationship_name($fieldName = '') {
		if (!$relationshipName = static::RelationshipName) {
			if ($relationshipName = static::name_from_class_name(static::related_class_name())) {
				$relationshipName = static::RelationshipPrefix . $relationshipName;
			}
		}
		return $relationshipName . ($fieldName ? ".$fieldName" : '');
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

		/** @var \GridField $gridField */
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
				new GridFieldOrderableRows(static::SortFieldName)
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
