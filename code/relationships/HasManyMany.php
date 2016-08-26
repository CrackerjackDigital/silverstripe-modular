<?php
namespace Modular\Relationships;

use Modular\GridField\GridField;

class HasManyMany extends GridField {
	const ShowAsTagsField = 'tags';
	const GridFieldConfigName = 'Modular\GridField\HasManyManyGridFieldConfig';

	// set this to the class names of classes which may be related via many_many to the extended model.
	private static $allowed_related_classes = [];

	/**
	 * Customise if shows as a GridField or a TagField depending on config.show_as
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
	 * Returns a field array using a tag field which can be used in derived classes instead of a GridField which is the default returned by cmsFields().
	 * @return array
	 */
	protected function tagFields() {
		$multipleSelect = (bool) $this->config()->get('multiple_select');
		$relatedClassName = static::RelatedClassName;

		return [
			(new \TagField(
				static::RelationshipName,
				null,
				$relatedClassName::get()
			))->setIsMultiple($multipleSelect)->setCanCreate(false),
		];
	}

	public function extraStatics($class = null, $extension = null) {
		$extra = [];

		if (static::sortable()) {
			$extra = [
				'many_many_extraFields' => [
					static::RelationshipName => [
						static::GridFieldOrderableRowsFieldName => 'Int',
					],
				],
			];
		}

		return array_merge_recursive(
			parent::extraStatics($class, $extension),
			$extra,
			[
				'many_many' => [
					static::RelationshipName => static::RelatedClassName,
				],
			]
		);
	}

	/**
	 * Allow override of the add new multiclass control to limit blocks to config.allowed_related_classes if set.
	 *
	 * @param $relationshipName
	 * @param $configClassName
	 * @return \Modular\GridField\GridFieldConfig
	 */
	protected function gridFieldConfig($relationshipName, $configClassName) {
		$config = parent::gridFieldConfig($relationshipName, $configClassName);

		// if config.allowed_classes is set then limit available classes to those listed there
		// check this extension first, then the extended model.
		$allowedClasses = $this->config()->get('allowed_related_classes')
			?: $this()->config()->get('allowed_related_classes');

		if ($allowedClasses) {
			/** @var \GridFieldAddNewMultiClass $addNewMultiClass */
			if ($addNewMultiClass = $config->getComponentByType('GridFieldAddNewMultiClass')) {
				$addNewMultiClass->setClasses($allowedClasses);
			}
		}
		return $config;
	}

}