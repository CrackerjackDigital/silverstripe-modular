<?php
namespace Modular\Fields;

use \HtmlEditorField;

class Synopsis extends Fields {
	const FieldName = 'Synopsis';

	private static $db = [
		self::FieldName => 'HTMLText',
	];
	public function cmsFields() {
		return [
			HtmlEditorField::create('Synopsis', 'Synopsis')->setRows(5),
		];
	}
}
