<?php
namespace Modular\Forms;

/**
 * Fix an issue where a DataTimeField with an empty time fails to save {@code DateTimeField::179} as the date is a string
 * so sprintf fails
 */
class TimeField extends \TimeField {
	private static $empty_value = '00:00:00';

	private $defaultValue = '';

	public function setDefaultValue($defaultValue) {
		$this->defaultValue = $defaultValue;
		return $this;
	}

	/**
	 * If no time set then return either the default value if set or the configured empty value.
	 * @return string
	 */
	public function dataValue() {
		return parent::dataValue() ?: ($this->defaultValue ?: $this->config()->get('empty_value'));
	}

}