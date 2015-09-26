<?php

trait bitfield {
	public function bitfieldTest($bitField, $flag) {
		return $bitField & $flag === $flag;
	}
}