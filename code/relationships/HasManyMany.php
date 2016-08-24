<?php
namespace Modular\Relationships;

use Modular\GridField\GridField;

class HasManyMany extends GridField {
	const GridFieldConfigName = 'Modular\GridField\HasManyManyGridFieldConfig';

	// set this to the class names of classes which may be related via many_many to the extended model.
	private static $allowed_related_classes = [];

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
		$allowedClasses = $this()->config()->get('allowed_related_classes')
			?: $this->config()->get('allowed_related_classes');

		if ($allowedClasses) {
			/** @var \GridFieldAddNewMultiClass $addNewMultiClass */
			if ($addNewMultiClass = $config->getComponentByType('GridFieldAddNewMultiClass')) {
				$addNewMultiClass->setClasses($allowedClasses);
			}
		}
		return $config;
	}

}