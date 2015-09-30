<?php
namespace Modular;

trait bitfield {
	public static function bitfieldTest($bitField, $flag) {
		return $bitField & $flag === $flag;
	}
}