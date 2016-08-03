<?php
namespace Modular;

class HasInternalLinkField extends HasFieldsExtension {
	const InternalLinkOption    = 'InternalLink';
	const InternalLinkFieldName = 'InternalLinkID';
	const RelationshipName = 'InternalLink';

	private static $has_one = [
		self::RelationshipName => 'SiteTree',
	];

	public function cmsFields() {
		return [
			(new DisplayLogicWrapper(
				new TreeDropdownField(self::InternalLinkFieldName, 'Link to', 'SiteTree')
			))->setName(self::InternalLinkFieldName)->setID(self::InternalLinkFieldName)
		];
	}

	public static function field_option() {
		return [self::InternalLinkFieldName => self::InternalLinkOption];
	}
}