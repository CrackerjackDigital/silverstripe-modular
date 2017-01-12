<?php
namespace Modular\Relationships;

use Modular\Fields\Field;

class HasOne extends Field {
	const RelationshipName    = '';
	const RelatedClassName    = '';
	const RelatedKeyField     = 'ID';
	const RelatedDisplayField = 'Title';

	private static $tab_name = 'Root.Main';

	/**
	 * Add a drop-down with related classes from RelatedClassName using RelatedKeyField and RelatedDisplayField.
	 *
	 * @return array
	 */
	public function cmsFields() {
		return [
			new \DropdownField(
				static::field_name(),
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
	public static function field_name($suffix = 'ID') {
		return static::RelationshipName . $suffix;
	}

	public static function related_class_name() {
		return static::RelatedClassName;
	}
	public function singleFieldValue() {
		return parent::singleFieldValue();
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