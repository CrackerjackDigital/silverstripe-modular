<?php
namespace Modular\GridList\Providers\Filters;

use Modular\Models\GridListFilter;
use Modular\Relationships\HasGridListFilters;

/**
 * Trait provides filters from children of the current page ordered by their frequency descending.
 */
trait children {
	abstract public function __invoke();

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
			return GridListFilter::get()->filter('ID', array_keys($counted));
		}
		return new \ArrayList();
	}
}