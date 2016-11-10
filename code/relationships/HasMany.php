<?php
namespace Modular\Relationships;

use Modular\GridField\GridField;
use Modular\Model;

class HasMany extends GridField {
	const GridFieldConfigName = 'Modular\GridField\HasManyGridFieldConfig';

	public function extraStatics($class = null, $extension = null) {
		return array_merge_recursive(
			parent::extraStatics($class, $extension) ?: [],
			[
				'has_many' => [
					static::RelationshipName => static::RelatedClassName
				]
			]
		);
	}
}