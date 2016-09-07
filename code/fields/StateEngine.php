<?php
namespace Modular\Fields;

use Modular\Model;

class StateEngineField extends EnumField {
	
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
	
	/**
	 * @return Model
	 */
	public function owner() {
		return parent::owner();
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