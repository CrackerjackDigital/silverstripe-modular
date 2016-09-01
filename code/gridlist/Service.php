<?php
namespace Modular\GridList;

use Modular\Object;

class Service extends Object {
	const FiltersClassName = 'Modular\GridList\Filters';

	public function mode() {
		return $this->Filters()->mode();
	}
	public function sort() {
		return $this->Filters()->sort();
	}
	protected function Filters() {
		return \Injector::inst()->get(static::FiltersClassName);
	}
}
