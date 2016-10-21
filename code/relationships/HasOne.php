<?php
namespace Modular\Relationships;

use Modular\Fields\Field;

class HasOne extends Field
{
	const ShowAsDropdown = '\DropdownField';
	
	const RelationshipName = '';
	const RelatedClassName = '';
	const RelatedKeyField = 'ID';
	const RelatedDisplayField = 'Title';
	
	private static $tab_name = 'Root.Main';
	
	private static $show_as = self::ShowAsDropdown;
	
	/**
	 * Add a drop-down with related classes from RelatedClassName using RelatedKeyField and RelatedDisplayField.
	 *
	 * @return array
	 */
	public function cmsFields()
	{
		$fieldClass = $this->config()->get('show_as');
		
		if (self::ShowAsDropdown == $fieldClass) {
			return [
				new $fieldClass(
					static::field_name(),
					static::relationship_name(),
					static::options()
				),
			];
		}
		
	}
	
	/**
	 * Return map of key field => title for the drop down where the relationship target can be chosen.
	 *
	 * @return array
	 */
	public static function options()
	{
		return \DataObject::get(static::RelatedClassName)
			->map(static::RelatedKeyField, static::RelatedDisplayField)
			->toArray();
	}
	
	/**
	 * Add has_one relationships to related class.
	 *
	 * @param null $class
	 * @param null $extension
	 * @return mixed
	 */
	public function extraStatics($class = null, $extension = null)
	{
		return array_merge_recursive(
			parent::extraStatics($class, $extension) ?: [ ],
			[
				'has_one' => [
					static::relationship_name() => static::related_class_name(),
				],
			]
		);
	}
	
	/**
	 * has_one relationships need an 'ID' appended to the relationship name
	 *
	 * @param string $append
	 * @return string
	 */
	public static function field_name($append = 'ID')
	{
		return static::relationship_name() . $append;
	}
	
	public static function relationship_name($fieldName = '')
	{
		return static::RelationshipName . ($fieldName ? ".$fieldName" : '');
	}
	
	public static function related_class_name($fieldName = '')
	{
		return static::RelatedClassName . ($fieldName ? ".$fieldName" : '');
	}
	
}