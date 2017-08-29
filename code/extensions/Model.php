<?php

namespace Modular;

use DataExtension;
use Modular\Traits\bitfield;
use DateField;
use FieldList;
use Modular\Traits\config;
use Modular\Traits\debugging;
use Modular\Traits\enabler;
use Modular\Traits\owned;
use Modular\Traits\related;

class ModelExtension extends DataExtension {
	use config;
	use enabler;
	use owned;
	use debugging;
	use related;
	use bitfield;

	const RemoveDBFields       = 1;
	const RemoveHasOneFields   = 2;
	const RemoveHasManyFields  = 4;
	const RemoveManyManyFields = 8;
	const RemoveAllFields      = 255;

	/**
	 * Return the extended model.
	 *
	 * @return \DataObject|\Modular\Model
	 */
	public function model() {
		return $this();
	}

	/**
	 * Workaround for PHP which doesn't do static::class
	 *
	 * @return string
	 */
	public static function class_name() {
		return get_called_class();
	}

	/**
	 * Writes the extended model and returns it if write returns truthish, otherwise returns null.
	 *
	 * @return \Modular\Model|null
	 * @throws \ValidationException
	 */
	public function writeAndReturn() {
		if ( $this()->write() ) {
			return $this();
		}

		return null;
	}

	/**
	 * Remove db, has_one etc fields from the field list which are defined in the extension, e.g. they may be replaced with a widget.
	 *
	 * @param \FieldList $fields
	 * @param int        $removeWhat
	 *
	 */
	protected static function remove_own_fields( \FieldList $fields, $removeWhat = self::RemoveAllFields ) {
		$config = \Config::inst()->forClass( get_called_class() );

		$fieldNames = [];
		if ( static::testbits( $removeWhat, self::RemoveDBFields ) ) {
			$fieldNames = array_merge(
				$fieldNames,
				array_keys( $config->get( 'db' ) ?: [] )
			);
		}
		if ( static::testbits( $removeWhat, self::RemoveHasOneFields ) ) {
			$fieldNames = array_merge(
				$fieldNames,
				array_map(
					function ( $item ) {
						return $item . 'ID';
					},
					array_keys( $config->get( 'has_one' ) ?: [] )
				)
			);
		}
		if ( static::testbits( $removeWhat, self::RemoveHasManyFields ) ) {
			$fieldNames = array_merge(
				$fieldNames,
				array_keys( $config->get( 'has_many' ) ?: [] )
			);
		}
		if ( static::testbits( $removeWhat, self::RemoveManyManyFields ) ) {
			$fieldNames = array_merge(
				$fieldNames,
				array_keys( $config->get( 'many_many' ) ?: [] )
			);
			$fieldNames = array_merge(
				$fieldNames,
				array_keys( $config->get( 'belongs_many_many' ) ?: [] )
			);
		}
		if ($fieldNames) {
			$fields->removeByName( array_filter( $fieldNames ), true );
		}
	}
}
