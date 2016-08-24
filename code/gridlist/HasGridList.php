<?php

namespace Modular\Extensions;

use Modular\GridList\Constraints;
use Modular\owned;

class HasGridList extends \Extension {
	use owned;

	public function GridList() {
		return new \ArrayData([
			'Items'   => $this->GridListItems(),
			'Filters' => $this->GridListFilters(),
			'Mode'    => $this->GridListMode(),
			'Sort'    => $this->GridListSort(),
		]);
	}

	public function GridListItems() {
		$items = [];
		$this()->extend('provideGridListItems', $items);

		/** @var Constraints $constraints */
		$constraints = \Injector::inst()->get('GridListFilterConstraints');

		if ($filter = $constraints->constraint('flt')) {
			if (isset($items[ $filter ])) {
				// if we have a filter requested then just use that filters items to merge in
				$items = [$filter => $items [ $filter ]];
			} else {
				// no items (bad filter?)
				$items = [];
			}
		}
		$out = new \ArrayList();

		foreach ($items as $filter => $list) {
			$out->merge($list);
		}
		return $out;
	}

	public function GridListFilters() {
		$filters = new \ArrayList();
		$this()->extend('provideGridListFilters', $filters);
		return $filters;
	}

	public function GridListSort() {
		return singleton('GridListFilterService')->sort();
	}

	public function GridListMode() {
		return singleton('GridListFilterService')->mode();
	}
}