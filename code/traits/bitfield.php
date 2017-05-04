<?php
namespace Modular\Traits;

trait bitfield {
	/**
	 * Check if the bit field has the provided bits set to 1. All provided test bits must be set.
	 *
	 * @param int $bitFieldToCheck
	 * @param int $bitsToTestAreSet
	 *
	 * @return bool
	 */
	public static function testbits($bitFieldToCheck, $bitsToTestAreSet) {
		return $bitsToTestAreSet === ( $bitFieldToCheck & $bitsToTestAreSet);
	}
}