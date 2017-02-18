<?php
namespace Modular\Relationships;

use Modular\Field;

class HasOne extends Field {
	const RelationshipName    = '';
	const RelatedClassName    = '';
	const RelatedKeyField     = 'ID';
	const RelatedDisplayField = 'Title';
	const Arity = 1;

	private static $tab_name = 'Root.Main';
	
	/**
	 * Add a drop-down with related classes from RelatedClassName using RelatedKeyField and RelatedDisplayField.
	 *
	 * @param $mode
	 * @return array
	 */
	public function cmsFields($mode) {
		return [
			new \DropdownField(
				static::related_field_name(),
				static::relationship_name(),
				static::options()
			),
		];
	}

	/**
	 * has_one relationships need an 'ID' appended to the relationship name to make the field name
	 *
	 * @param string $suffix defaults to 'ID'
	 * @return string
	 */
	public static function related_field_name($suffix = 'ID') {
		return static::RelationshipName . $suffix;
	}

	/**
	 * Return unadorned has_one related class name.
	 *
	 * @return string
	 */
	public static function related_class_name() {
		return static::RelatedClassName;
	}

	/**
	 * Returns the RelationshipName for this field if set, optionally appended with the fieldName as for a relationship.
	 *
	 * @param string $fieldName if supplied will be added on to RelationshipName with a '.' prefix
	 * @return string
	 */
	public static function relationship_name($fieldName = '') {
		return static::RelationshipName ? (static::RelationshipName . ($fieldName ? ".$fieldName" : '')) : '';
	}

	/**
	 * Return map of key field => title for the drop down where the relationship target can be chosen.
	 *
	 * @return array
	 */
	public static function options() {
		return \DataObject::get(static::RelatedClassName)->map(static::RelatedKeyField, static::RelatedDisplayField)->toArray();
	}

	/**
	 * Add has_one relationships to related class.
	 *
	 * @param null $class
	 * @param null $extension
	 * @return mixed
	 */
	public function extraStatics($class = null, $extension = null) {
		return array_merge_recursive(
			parent::extraStatics($class, $extension) ?: [],
			[
				'has_one' => [
					static::relationship_name() => static::related_class_name(),
				],
			]
		);
	}
}