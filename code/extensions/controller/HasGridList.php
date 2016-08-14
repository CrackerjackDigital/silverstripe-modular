<?php
namespace Modular\Extensions\Controller;

use Modular\ContentControllerExtension;

class HasGridList extends ContentControllerExtension {
	public function GridList() {
		return new \ArrayData([
			'Items'   => $this->GridListItems(),
			'Filters' => $this->GridListFilters(),
			'Mode'    => $this->GridListMode(),
			'Sort'    => $this->GridListSort()
		]);
	}

	public function GridListItems() {
		$items = new \ArrayList();
		$this()->extend('provideGridListItems', $items);
		return $items;
	}

	public function GridListFilters() {
		$items = new \ArrayList();
		$this()->extend('provideGridListFilters', $items);
		return $items;
	}

	public function GridListSort() {
		return singleton('GridListService')->sort();
	}

	public function GridListMode() {
		return singleton('GridListService')->mode();
	}
}
