<?php
namespace Modular;

use Modular\Fields\Field;
use SS_List;

/**
 * @method SS_List Links
 */
class Links extends Field {
	const RelationshipName = 'Links';

	private static $many_many = [
		self::RelationshipName => 'LinkAttribute',
	];

	private static $many_many_extraFields = [
		self::RelationshipName => [
			self::GridFieldOrderableRowsFieldName => 'Int',
		],
	];

	public function cmsFields() {
		return $this()->isInDB()
			? [ $this->gridField() ]
			: [ $this->saveMasterHint() ];
	}

	public function onAfterPublish() {
		/** @var Model|\Versioned $link */
		foreach ($this()->Links() as $link) {
			if ($link->hasExtension('Versioned')) {
				$link->publish('Stage', 'Live', false);
			}
		}
	}
}