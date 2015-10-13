<?php
namespace Modular;

trait bitfield {
	public static function bitfieldTest($bitFieldToTest, $flag) {
		return $flag === ($bitFieldToTest & $flag);
	}
}