<?php
namespace Modular\Relationships;

use \Modular\Fields\Field;

class HasMany extends Field {
	const RelationshipName    = '';
	const RelatedClassName    = '';
	const GridFieldConfigName = 'Modular\GridField\HasManyGridFieldConfig';

	public function extraStatics($class = null, $extension = null) {
		$parent = parent::extraStatics($class, $extension);
		return array_merge_recursive(
			$parent,
			[
				'has_many' => [
					static::RelationshipName => static::RelatedClassName
				]
			]
		);
	}

	public function cmsFields() {
		return [
			$this->gridField(
				static::RelationshipName,
				static::GridFieldConfigName
			),
		];
	}
}