<?php
namespace Modular\Fields;

use TextField;

class ExternalLink extends Field {
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