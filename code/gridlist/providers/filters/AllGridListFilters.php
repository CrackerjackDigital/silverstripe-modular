<?php
namespace Modular\GridList\Providers\Filters;

use Modular\GridList\Interfaces\FiltersProvider;
use Modular\ModelExtension;
use Modular\Models\GridListFilter;

/**
 * Returns all filters (GridListFilter models) from the database.
 *
 * @package Modular\GridList\Providers\Filters
 */
class AllGridListFilters extends ModelExtension implements FiltersProvider {
	public function provideGridListFilters() {
		return GridListFilter::get()->sort('Sort');
	}
}