<?php
namespace Modular\Relationships;

use Modular\Fields\GridField;
use Modular\Model;

class ManyMany extends GridField {
	const RelationshipName    = '';
	const RelatedClassName      = '';
	const GridFieldConfigName = 'Modular\GridField\HasManyManyGridFieldConfig';

	private static $allowed_related_classes = [];

	public function extraStatics($class = null, $extension = null) {
		$parent = parent::extraStatics($class, $extension) ?: [];

		$extra = [];

		if ($this->config()->get('sortable')) {
			$extra = [
				'many_many_extraFields' => [
					static::RelationshipName => [
						static::GridFieldOrderableRowsFieldName => 'Int',
					],
				],
			];
		}

		return array_merge_recursive(
			$parent,
			$extra,
			[
				'many_many'             => [
					static::RelationshipName => static::RelatedClassName,
				],
			]
		);
	}

	/**
	 * Allow override of the add new multiclass control to limit blocks to config.allowed_block_classes if set.
	 *
	 * @param $relationshipName
	 * @param $configClassName
	 * @return \Modular\GridField\GridFieldConfig
	 */
	protected function gridFieldConfig($relationshipName, $configClassName) {
		$config = parent::gridFieldConfig($relationshipName, $configClassName);

		// if config.allowed_classes is set then limit available classes to those listed there
		$allowedClasses = $this->config()->get('allowed_related_classes');
		if ($allowedClasses) {
			/** @var \GridFieldAddNewMultiClass $addNewMultiClass */
			if ($addNewMultiClass = $config->getComponentByType('GridFieldAddNewMultiClass')) {
				$addNewMultiClass->setClasses($this->config()->get('allowed_related_classes'));
			}
		}
		return $config;
	}

}