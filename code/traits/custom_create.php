<?php
namespace Modular\Traits;

trait custom_create {
	public static function custom_create($args = []) {
		return \Injector::inst()->createWithArgs(static::custom_class_name(), $args);
	}

	private static function custom_class_name() {
		return \Config::inst()->get(get_called_class(), 'custom_class_name') ?: get_called_class();
	}
}