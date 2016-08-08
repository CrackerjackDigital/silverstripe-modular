<?php
namespace Modular;

trait owned {
	public function __invoke() {
		return $this->owner();
	}

	/**
	 * @return mixed
	 */
	public function owner() {
		return $this->owner;
	}
}