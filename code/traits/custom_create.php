<?php
namespace Modular\Traits;

/**
 * Return an instance of class nominated in the called classes config.custom_class_name or
 * the called class if not set. This can be called from a create method overriding the standard silverstripe
 * create method.
 *
 * @package Modular\Traits
 */
trait custom_create {
	/**
	 * Will override in exhibiting classes which don't have a create.
	 * @return mixed
	 */
	public static function create() {
		return static::custom_create(func_get_args());
	}

	/**
	 * Call this explicitly in class create if needed.
	 * @param array $args
	 * @return mixed
	 */
	public static function custom_create($args = []) {
		return \Injector::inst()->createWithArgs(static::custom_class_name(), $args);
	}

}