<?php
namespace Modular;

trait bitfield {
	public static function bitfieldTest($bitField, $flag) {
		return $flag === ($bitField & $flag);
	}
}