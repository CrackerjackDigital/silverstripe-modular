<?php
namespace Modular\Traits;

trait microtimestamp {
	public static function microtimestamp() {
		return microtime(true);
	}
}