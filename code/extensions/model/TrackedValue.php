<?php

namespace Modular\Extensions\Model;

use Modular\ModelExtension;

/**
 * Track a field value across writes and re-reads. Only tracks the previous generation changed
 * value not a full history.
 *
 * @package Modular\Extensions\Model
 */
class TrackedValue extends ModelExtension {
	// fields added to models for previous values will be prefixed with this,
	// e.g for field 'Title' a field will be added to model called '_TrackedTitle'
	const TrackedFieldPrefix = '_Tracked';

	/**
	 * field name to track on model, e.g in config.yml below will be 'Title'
	 *
	 * Model:
	 *   extensions:
	 *     - TrackedValue('Title')
	 *
	 * @var string
	 */
	private $fieldName;

	/**
	 * registry of all fields being tracked shared by all instances of TrackedValue
	 * fields will be added here dynamically (or can be configured here) as e.g:
	 *      [
	 *          'Model' => [
	 *              'Title' => '_TrackedTitle'
	 *          ]
	 *      ]
	 *
	 * @var array
	 */
	private static $field_registry = [

	];

	/**
	 * Return the previous value of a field from last time it changed from the model
	 * _TrackedFieldName field added during build.
	 *
	 * @param string $fieldName
	 *
	 * @return mixed
	 */
	public function trackedValue( $fieldName ) {
		$fieldName = static::TrackedFieldPrefix . $fieldName;

		return $this()->{$fieldName};
	}

	/**
	 * Called on config manifest build with args set to the parameters from config.yml file,
	 * in this case args[0] will be the field name to track, so save that.
	 *
	 * @param string $class
	 * @param string $extensionClass
	 * @param array  $args with args[0] being the field name in config file
	 */
	public static function add_to_class( $class, $extensionClass, $args = null ) {
		if ( func_num_args() ) {
			$fieldNames = array_filter(is_array($args[0]) ? $args[0] : explode(',', $args[0]));
			foreach ($fieldNames as $fieldName) {
				static::$field_registry[ $class ][ $fieldName ] = static::TrackedFieldPrefix . $fieldName;
			}

		}
		parent::add_to_class( $class, $extensionClass, $args );
	}

	/**
	 * Add fields to the model for each tracked value.
	 *
	 * TODO figure out a way to get a real field type not just using 'Text'
	 *
	 * @param string $class
	 * @param string $extension
	 *
	 * @return array
	 */
	public function extraStatics( $class = null, $extension = null ) {
		$fields = array_fill_keys(
			static::fields_for_class( $class ),
			'Text'
		);

		return array_merge(
			parent::extraStatics( $class, $extension ) ?: [],
			[
				'db' => $fields,
			]
		);
	}

	/**
	 * Return the map of fields being tracked for the provided class.
	 *
	 * @param string $class
	 *
	 * @return array|mixed
	 */
	private static function fields_for_class( $class ) {
		return isset( static::$field_registry[ $class ] )
			? static::$field_registry[ $class ]
			: [];
	}

	/**
	 * If values of fields being tracked have changed then add the original values
	 * to e.g. _TrackedTitle so can be recovered after write or reload of the model.
	 *
	 * Only one previous generation of value is kept for each change.
	 */
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