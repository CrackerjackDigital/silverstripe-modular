<?php
namespace Modular;

class HasContentField extends HasFieldsExtension {
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