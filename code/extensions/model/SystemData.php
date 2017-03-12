<?php
namespace Modular\Extensions\Model;

use Modular\Traits\enabler;
use Modular\TypedField;
use Modular\Types\BoolType;
use SQLQuery;

/**
 * This extension provides a flag for data which may have 'System' significance and so
 * should generally not be returned for the user to view and/or choose, e.g. a RelationshipType
 * may only be useable by the system and not selectable in a dropdown.
 */
class SystemData extends TypedField implements BoolType {
	use enabler;

	const Name = 'SystemDataFlag';
	const YesValue  = 1;
	const NoValue   = 0;

	// can be set to false if all values are required to be returned, e.g. when building the RelationshipType table
	// we need to be able to check for existing System records.
	private static $enabled = true;

	public function augmentSQL(SQLQuery &$query) {
		if (static::enabled()) {
			if (!\Permission::check('ADMIN')) {
				$query->addWhere( '"' . $this()->baseTable() . '"."' . static::field_name() . '" = ' . self::NoValue );
			}
		}
		parent::augmentSQL($query);
	}
}