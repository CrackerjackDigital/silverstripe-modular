<?php
namespace Modular;

trait owned {
	public function __invoke() {
		return $this->owner();
	}

	/**
	 * @return Model|\DataObject
	 */
	public function owner() {
		return $this->owner;
	}
}