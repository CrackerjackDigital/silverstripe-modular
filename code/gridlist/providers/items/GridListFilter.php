<?php
namespace Modular\GridList\Providers\Items;

use Modular\GridList\Interfaces\ItemsProvider;
use Modular\ModelExtension;
use Modular\Relationships\HasGridListFilters;

/**
 * Add to a GridList host (e.g. GridListBlock) to provide all items across the site which match the filters added to the block.
 *
 * @package Modular\GridList\Providers
 */
class GridListFilter extends ModelExtension implements ItemsProvider {
	public function provideGridListItems() {
		if ($this()->hasExtension(HasGridListFilters::class_name())) {
			$filterIDs = array_keys(
				$this()->{HasGridListFilters::relationship_name()}()->map()->toArray()
			);
			$filterField = HasGridListFilters::relationship_name('ID');

			return \Page::get()->filter([
				$filterField => $filterIDs
			]);
		}
	}
}