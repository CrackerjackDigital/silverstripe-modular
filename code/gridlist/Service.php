<?php
namespace Modular\GridList;

use Modular\Object;

class Service extends Object {
	const ConstraintsClassName = 'Modular\GridList\Constraints';

	public function mode() {
		return $this->constraints()->mode();
	}
	public function sort() {
		return $this->constraints()->sort();
	}
	protected function constraints() {
		return \Injector::inst()->get(static::ConstraintsClassName);
	}
}
