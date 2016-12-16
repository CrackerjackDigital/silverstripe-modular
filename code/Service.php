<?php
namespace Modular;

use Modular\Interfaces\Notification;

class Service extends Object {
	const ServiceName = '';

	public static function factory() {
		return \Injector::inst()->get(static::ServiceName ?: get_called_class(), true, func_get_args());
	}
}