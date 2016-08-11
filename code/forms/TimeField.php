<?php
namespace Modular\Forms;

/**
 * Fix an issue where a DataTimeField with an empty time fails to save {@code DateTimeField::179} as the date is a string
 * so sprintf fails
 */
class TimeField extends \TimeField {
	/**
	 * If no time set then return midnight for start of day.
	 * @return string
	 */
	public function dataValue() {
		return parent::dataValue() ?: '00:00:00';
	}
}