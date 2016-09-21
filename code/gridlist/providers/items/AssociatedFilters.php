<?php
namespace Modular\GridList\Providers\Items;

use Modular\Fields\Field;
use Modular\GridList\Interfaces\ItemsProvider;
use Modular\Relationships\HasGridListFilters;

/**
 * Add to a GridList host (e.g. GridListBlock) to provide all items across the site which match the filters added to the block.
 *
 * @package Modular\GridList\Providers
 */
class AssociatedFilters extends Field implements ItemsProvider {
	const SingleFieldName   = 'ProvideAssociatedFilters';
	const SingleFieldSchema = 'Boolean';

	private static $defaults = [
		self::SingleFieldName => true
	];

	public function provideGridListItems() {
		if ($this()->{self::SingleFieldName}) {
			if ($this()->hasExtension(HasGridListFilters::class_name())) {
				$filterIDs = array_keys(
					$this()->{HasGridListFilters::relationship_name()}()->map()->toArray()
				);
				$filterField = HasGridListFilters::relationship_name('ID');

				return \Page::get()->filter([
					$filterField => $filterIDs,
				]);
			}
		}
	}
}