<?php
namespace Modular\Traits;

trait timestamp {
	/**
	 * @param null $init not used
	 * @return int
	 */
	public static function timestamp($init = null) {
		return time();
	}
}