<?php
namespace Modular\GridList\Providers\Filters;
use Modular\Models\GridListFilter;

/**
 * Trait provides default GridListFilters
 *
 * @package Modular\GridList\Providers\Filters
 */
trait defaults {
	public function provideGridListFilters() {
		// now we add the default filter on to fill in any space

		return GridListFilter::get()->sort('Sort');

	}
}