<?php
namespace Modular\Traits;

trait bitfield {
	public static function testbits($bitFieldToCheck, $bitsToTestAreSet) {
		return $bitsToTestAreSet === ( $bitFieldToCheck & $bitsToTestAreSet);
	}
}