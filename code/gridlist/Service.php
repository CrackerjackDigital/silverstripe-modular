<?php
namespace Modular\GridList;

use Modular\Admin\GridListFilters;
use Modular\Models\GridListFilter;
use Modular\Object;

class Service extends Object {
	const FiltersClassName = 'Modular\GridList\Filters';


	public function constraint($name, $persistance = Filters::SessionSaveGlobal) {
		return $this->Filters()->constraint($name, $persistance);
	}

	public function mode() {
		return $this->Filters()->mode();
	}
	public function sort() {
		return $this->Filters()->sort();
	}

	/**
	 * Allow calls statically through to Filters as it's easier then
	 * @return Filters
	 */
	public static function Filters() {
		return \Injector::inst()->get(static::FiltersClassName);
	}
}
