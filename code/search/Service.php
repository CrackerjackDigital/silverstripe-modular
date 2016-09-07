<?php
namespace Modular\Search;

class Service extends \Modular\Object {
	const FiltersClassName = 'Modular\Search\Filters';

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
