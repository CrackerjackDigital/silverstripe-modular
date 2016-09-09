<?php
namespace Modular\Fields;

abstract class EnumField extends Field {
	const SingleFieldName = '';
	
	/**
	 * Can be numerically indexed or associative.
	 * If numeric, then values will be used as both key and value in dropdown
	 * If assoc then key will be value and value will be display in dropdown
	 *
	 * First option will be the default
	 */
	
	private static $options = [];
	
	public static function field_schema()
	{
		$options = array_keys(static::all_options());
		return "Enum('" . implode(',', $options) . "')";
	}
	
	public function cmsFields()
	{
		return [
			new \DropdownField(
				static::field_name(),
				null,
				$this->dropdownMap()
			)
		];
	}
	
	public function dropdownMap()
	{
		return static::all_options();
	}
	
	public static function all_options() {
		if ($options = static::config()->get('options')) {
			if (is_numeric(key($options))) {
				$options = array_combine(
					array_values($options),
					array_values($options)
				);
			}
		}
		return $options ?: [];
	}
	
}