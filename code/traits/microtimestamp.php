<?php
namespace Modular\Traits;

trait microtimestamp {
	public static function microtimestamp($asFloat = false) {
		return microtime($asFloat);
	}
}