<?php
namespace Modular;

trait owned {
	public function __invoke() {
		return $this->owner();
	}
	public function owner() {
		return $this->owner;
	}
}