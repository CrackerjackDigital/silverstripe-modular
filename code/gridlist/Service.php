<?php
namespace Modular\GridList;

use Modular\Object;

class Service extends Object {
	/** @var  Constraints */
	protected $constraints;

	public function setConstraints($constraints) {
		$this->constraints = $constraints;
	}
	public function mode() {
		return $this->constraints->mode();
	}
	public function sort() {
		return $this->constraints->sort();
	}
}
