<?php
namespace Modular\GridList;

use Modular\Admin\GridListFilters;
use Modular\Models\GridListFilter;
use Modular\Object;

class Service extends Object {
	const FiltersClassName = 'Modular\GridList\Filters';

	public function mode() {
		return $this->Filters()->mode();
	}
	public function sort() {
		return $this->Filters()->sort();
	}

	/**
	 * @return Filters
	 */
	protected function Filters() {
		return \Injector::inst()->get(static::FiltersClassName);
	}
}
