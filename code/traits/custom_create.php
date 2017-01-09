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
	public static function custom_create($args = []) {
		return \Injector::inst()->createWithArgs(static::custom_class_name(), $args);
	}

	private static function custom_class_name() {
		return \Config::inst()->get(get_called_class(), 'custom_class_name') ?: get_called_class();
	}
}