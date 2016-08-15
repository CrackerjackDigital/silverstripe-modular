<?php
namespace Modular\Relationships;

use \Modular\Fields\Field;

class HasSlides extends Field {
	private static $has_many = [
		'Slides' => 'Modular\Models\Slide'
	];
	public function cmsFields() {
		return [
			$this->gridField(
				'Slides'
			)
		];
	}
}