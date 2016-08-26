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
		$out = new \ArrayList();
		
		if ($allItems = $this()->invokeWithExtensions('provideGridListItems')) {
			foreach ($allItems as $extensionItems) {
				foreach ($extensionItems as $model) {
					$out->merge($model);
				}
			}
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