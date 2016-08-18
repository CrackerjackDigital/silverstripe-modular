<?php
namespace Modular\Fields;

use DisplayLogicWrapper;
use TreeDropdownField;

class InternalLink extends Field {
	const InternalLinkOption    = 'InternalLink';
	const InternalLinkFieldName = 'InternalLinkID';
	const RelationshipName      = 'InternalLink';

	private static $has_one = [
		self::RelationshipName => 'SiteTree',
	];

	public function cmsFields() {
		return [
			(new DisplayLogicWrapper(
				new TreeDropdownField(self::InternalLinkFieldName, 'Link to', 'SiteTree')
			))->setName(self::InternalLinkFieldName)->setID(self::InternalLinkFieldName),
		];
	}
}