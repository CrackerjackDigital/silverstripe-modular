<?php
namespace Modular\GridList\Providers\Items;

use Modular\Fields\Field;
use Modular\Fields\ModelTag;
use Modular\GridList\Interfaces\ItemsProvider;
use Modular\GridList\VersionedHasGridListFilters as HasGridListFilters;

/**
 * Add to a GridList host (e.g. GridListBlock) to provide all items across the site which match the filters added to the block.
 * Add a field which enables this to be enabled/disabled in CMS.
 *
 * @package Modular\GridList\Providers
 */
class AssociatedFilters extends Field implements ItemsProvider {
	const SingleFieldName   = 'ProvideAssociatedFilters';
	const SingleFieldSchema = 'Boolean';

	private static $defaults = [
		self::SingleFieldName => true,
	];

	/**
	 * Add the model tag of the current page so can use in decoration for enable/disable field.
	 * @return mixed
	 */
	public function fieldDecorationTokens() {
		return array_merge(
			parent::fieldDecorationTokens(),
			[
				'modelTag' => \Director::get_current_page()->{ModelTag::SingleFieldName}
			]
		);
	}

	/**
	 * Provide pages which have
	 * @return \DataList
	 */
	public function provideGridListItems() {
		if ($this()->{static::SingleFieldName}) {
			if ($this()->hasExtension(HasGridListFilters::class_name())) {
				$filterIDs = $this()->{HasGridListFilters::relationship_name()}()->column('ID');
				$filterField = HasGridListFilters::relationship_name('ID');

				return \Page::get()->filter([
					$filterField => $filterIDs,
				]);
			}
		}
	}
}