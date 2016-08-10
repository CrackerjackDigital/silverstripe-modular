<?php
namespace Modular\Fields;

use HtmlEditorField;

class Content extends Field {
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