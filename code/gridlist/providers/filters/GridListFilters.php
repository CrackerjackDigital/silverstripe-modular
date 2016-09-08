<?php
namespace Modular\GridList\Providers\Filters;

use Modular\GridList\Interfaces\FiltersProvider;
use Modular\ModelExtension;
use Modular\Models\GridListFilter;

class GridListFilters extends ModelExtension implements FiltersProvider {
	public function provideGridListFilters() {
		// now we add the default filter on to fill in any space

		return GridListFilter::get()->sort('Sort');
	}

}