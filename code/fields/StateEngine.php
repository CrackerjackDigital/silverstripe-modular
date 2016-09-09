<?php
namespace Modular\Fields;

use Modular\Model;

/**
 * Class which only allows certain transitions for this fields values as defined by config.states. Adds a field
 * to extended module <ClassName>StateUpdated
 * @package Modular\Fields
 */
class StateEngineField extends EnumField {
	const UpdatedFieldPostfix = 'StateUpdated';
	/**
	 * Array of states to array of valid 'next' states.
	 *
	 * @var array
	 */
	private static $states = [
		#   self::State1 => [
		#       self::State2,
		#       self::State4,
		#   ],
		#   self::State2 => [
		#       self::State3,
		#       self::State4
		#   ],
		#   self::State3 => [
		#       self::State2
		#       self::State4
		#   ],
		#   self::State4 => [
		#
		#   ]
	];
	
	public function cmsFields() {
		return array_merge(
			parent::cmsFields(),
			[
				static::date_field_name() =>
			]
		);
	}
	
	/**
	 * Adds <ClassName>StateUpdated field as SS_DateTime.
	 * @param null $class
	 * @param null $extension
	 * @return array
	 */
	public function extraStatics($class = null, $extension = null)
	{
		return array_merge_recursive(
			parent::extraStatics($class, $extension),
			[
				static::date_field_name() => 'SS_DateTime'
			]
		);
	}
	
	public static function date_field_name() {
		return get_called_class() . static::UpdatedFieldPostfix;
	}
	
	
	public function dropdownMap()
	{
		$options = static::all_options();
		
		if ($this()->isInDB()) {
			$current = $this()->{static::field_name()};
			return $options[$current];
		} else {
			return current($options);
		}
	}
	
	/**
	 * Check that the new state being requested is valid from the current state.
	 * @param \ValidationResult $result
	 * @return array
	 * @throws \ValidationException
	 */
	public function validate(\ValidationResult $result)
	{
		$fieldName = static::field_name();
		
		if ($this->owner()->isChanged($fieldName)) {
			$states = static::states();
			
			$new = $this()->{$fieldName};
			$original = $this->owner()->getChangedFields()[$fieldName]['before'];
			
			if (!in_array($new, $states[$original])) {
				$result->error("Can't go from state '$original' to '$new'");
				throw new \ValidationException($result);
			}
		}
		return parent::validate($result);
	}
}