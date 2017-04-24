<?php

namespace Modular\Extensions\Model;

use Modular\ModelExtension;

/**
 * Track a value across writes
 *
 * @package Modular\Extensions\Model
 */
class TrackedValue extends ModelExtension {
	const TrackedFieldPrefix = '_Tracked';

	private $fieldName;

	private static $field_registry = [

	];

	public function trackedValue( $fieldName ) {
		$fieldName = static::TrackedFieldPrefix . $fieldName;

		return $this()->{$fieldName};
	}

	public static function add_to_class( $class, $extensionClass, $args = null ) {
		if ( func_num_args() ) {
			static::$field_registry[ $class ][ $args[0] ] = static::TrackedFieldPrefix . $args[0];
		}
		parent::add_to_class( $class, $extensionClass, $args );
	}

	public function extraStatics( $class = null, $extension = null ) {
		$fields = array_fill_keys(
			static::fields_for_class( $class ),
			'Varchar(255)'
		);

		return array_merge(
			parent::extraStatics( $class, $extension ) ?: [],
			[
				'db' => $fields,
			]
		);
	}

	private static function fields_for_class( $class ) {
		return isset( static::$field_registry[ $class ] )
			? static::$field_registry[ $class ]
			: [];
	}

	public function onBeforeWrite() {
		$class = get_class( $this() );

		if ( $changed = $this()->getChangedFields() ) {
			$track = array_intersect_key(
				$changed,
				static::fields_for_class( $class )
			);

			foreach ( $track as $name => $info ) {
				if ( isset( $fieldsForClass[ $name ] ) ) {
					$trackingFieldName            = static::TrackedFieldPrefix . $name;
					$this()->{$trackingFieldName} = $changed[ $name ]['before'];
				}
			}
		}
	}
}