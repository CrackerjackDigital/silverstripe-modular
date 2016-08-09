<?php
namespace Modular\Fields;

use HtmlEditorField;

class Content extends Fields {
	const ContentFieldName = 'Content';

	private static $db = [
		self::ContentFieldName => 'HTMLText'
	];

	public function cmsFields() {
		return [
			new HtmlEditorField(self::ContentFieldName)
		];
	}
}