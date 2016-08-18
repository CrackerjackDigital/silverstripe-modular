<?php

namespace Modular\Relationships;

use Modular\Fields\Field;

class ManyMany extends Field {
	const RelationshipName    = '';
	const RelatedClassName    = '';
	const GridFieldConfigName = '';

	private static $cms_tab_name = 'Root.ContentBlocks';

	public function extraStatics($class = null, $extension = null) {
		$parent = parent::extraStatics($class, $extension) ?: [];

		$extra = [];

		if ($this->orderable()) {
			$extra = array_merge_recursive(
				$extra,
				[
					'many_many_extraFields' => [
						static::RelationshipName => [
							static::GridFieldOrderableRowsFieldName => 'Int',
						],
					],
				]
			);
		}
		return array_merge_recursive(
			$parent,
			$extra,
			[
				'many_many' => [
					static::RelationshipName => static::RelatedClassName,
				],
			]
		);
	}

	public function cmsFields() {
		return $this()->isInDB()
			? [$this->gridField(static::RelationshipName)]
			: [$this->saveMasterHint()];
	}

}