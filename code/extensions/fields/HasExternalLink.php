<?php
namespace Modular;

class HasExternalLinkField extends HasFieldsExtension {
	const ExternalLinkOption    = 'ExternalLink';
	const ExternalLinkFieldName = 'ExternalLink';
	const RelationshipName      = 'ExternalLink';

	private static $db = [
		self::RelationshipName => 'Text',
	];

	public function cmsFields() {
		return [
			new TextField(self::ExternalLinkFieldName),
		];
	}

	public static function field_option() {
		return [self::ExternalLinkFieldName => self::ExternalLinkOption];
	}

}