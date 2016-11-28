<?php
namespace Modular;

class Service extends Object {
	use trackable;
	const ServiceName = '';

	public static function factory() {
		return \Injector::inst()->get(static::ServiceName ?: get_called_class(), true, func_get_args());
	}

}