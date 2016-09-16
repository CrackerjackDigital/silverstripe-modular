<?php
namespace Modular\GridList\Providers\Filters;

use Modular\GridList\Interfaces\FiltersProvider;
use Modular\ModelExtension;
use Modular\Models\GridListFilter;
use Modular\Relationships\HasGridListFilters;

class ChildPages extends ModelExtension implements FiltersProvider {

	/**
	 * @return \ArrayList|\DataList
	 */
	public function provideGridListFilters() {
		$pages = $this()->Children();
		$counted = [];

		foreach ($pages as $page) {
			if ($page->hasMethod(HasGridListFilters::RelationshipName)) {
				$filters = $page->related(HasGridListFilters::RelationshipName);
				foreach ($filters as $filter) {
					if (isset($counted[ $filter->Title ])) {
						$counted[ $filter->ID ]++;
					} else {
						$counted[ $filter->ID ] = 1;
					}
				}
			}
		}
		if ($counted) {
			asort($counted);
//			return GridListFilter::get()->filter('ID', array_keys($counted));
		}
		return new \ArrayList();
	}
}