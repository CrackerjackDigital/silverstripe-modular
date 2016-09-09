<?php
namespace Modular;

trait owned {
	public function __invoke() {
		return $this->owner();
	}

	/**
	 * @return Model|Controller|\DataObject
	 */
	public function owner() {
		return $this->owner;
	}
}