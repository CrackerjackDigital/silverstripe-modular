<?php
namespace Modular\Relationships;

use \Modular\Fields\Field;

class HasSlides extends Field {
	const RelationshipName    = 'Slides';
	const RelatedClassName    = 'Modular\Models\Slide';
	const GridFieldConfigName = 'Modular\GridField\GridFieldConfig';

	private static $has_many = [
		self::RelationshipName => 'Modular\Models\Slide',
	];

	public function cmsFields() {
		return [
			$this->gridField(
				static::RelationshipName
			),
		];
	}
}