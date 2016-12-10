<?php
namespace Modular;

trait bitfield {
	public static function bitfieldTest($bitFieldToCheck, $bitToTestIsSet) {
		return $bitToTestIsSet === ($bitFieldToCheck & $bitToTestIsSet);
	}
}