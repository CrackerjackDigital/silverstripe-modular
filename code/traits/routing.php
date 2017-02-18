<?php
namespace Modular\Traits;

trait routing {
	public static function class_name_to_route($className) {
		return strtolower(str_replace('\\', '/', $className));
	}
}