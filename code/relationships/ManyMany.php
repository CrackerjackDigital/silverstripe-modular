<?php
namespace Modular\Relationships;

use Modular\Fields\Field;
use Modular\Model;

class ManyMany extends Field {
	const RelationshipName    = '';
	const RelatedClassName      = '';
	const GridFieldConfigName = 'Modular\GridField\HasManyManyGridFieldConfig';

	private static $cms_tab_name = '';

	private static $allowed_related_classes = [];

	public function extraStatics($class = null, $extension = null) {
		$parent = parent::extraStatics($class, $extension) ?: [];

		return array_merge_recursive(
			$parent,
			[
				'many_many'             => [
					static::RelationshipName => static::RelatedClassName,
				],
				'many_many_extraFields' => [
					static::RelationshipName => [
						static::GridFieldOrderableRowsFieldName => 'Int',
					],
				],
			]
		);
	}

	/**
	 * If model is saved then a gridfield, otherwise a 'save master first' hint.
	 * @return array
	 */
	public function cmsFields() {
		return $this()->isInDB()
			? [$this->gridField()]
			: [$this->saveMasterHint()];
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