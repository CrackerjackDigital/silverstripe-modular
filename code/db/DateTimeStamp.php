<?php

namespace Modular\DB;

use DateTime;
use DB;
use SS_Datetime;

/**
 * INCOMPLETE DateTimeStamp provides a higher-resolution timer than DateTime.
 *
 * TODO how to check for mysql version 5.7+ where fractional part of
 *      DATETIME is supported e.g. DATETIME(6). Maybe use a decimal with
 *      time
 *
 * @package Modular\DB
 */
class DateTimeStamp extends SS_DateTime {

	/**
	 * Add the field to the underlying database.
	 */
	public function requireField() {
		DB::create_field(
			$this->tableName,
			$this->name,
			'Decimal(9,4)'
		);
	}

	public function setValue( $value, $record = null ) {
		if ( $value ) {
			if ( ! is_numeric( $value ) ) {
				// maybe a date string
				if ( preg_match( '#^([0-9]+)/([0-9]+)/([0-9]+)$#', $value, $parts ) ) {
					$date        = new DateTime( $value );
					$this->value = $date->getTimestamp();
				} elseif ( preg_match( '#^([0-9]+)/([0-9]+)/([0-9]+)$#', $value, $parts ) ) {

				}
			} else {
				$this->value = $value;
			}
		} else {
			$this->value = null;
		}
	}

	/**
	 * Overload this so works with numeric value.
	 *
	 * @param string $format
	 *
	 * @return string
	 */
	public function Format( $format ) {
		if ( $this->value ) {
			$date = new DateTime();
			$date->setTimestamp( $this->value );

			return $date->format( $format );
		}
	}
}