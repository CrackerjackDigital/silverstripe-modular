<?php
namespace Modular\GridList\Providers\Items;

use Modular\Fields\Field;
use Modular\Fields\ModelTag;
use Modular\GridList\Interfaces\ItemsConstraints;
use Modular\GridList\Interfaces\ItemsProvider;
use Modular\Models\GridListFilter;
use Modular\Relationships\HasGridListFilters;

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
	 *
	 * @return mixed
	 */
	public function fieldDecorationTokens() {
		return array_merge(
			parent::fieldDecorationTokens(),
			[
				'modelTag' => \Director::get_current_page()->{ModelTag::SingleFieldName},
			]
		);
	}

	/**
	 * Return the ids of filters defined on the GridList for the current block/page.
	 *
	 * @return array
	 */
	protected function filterIDs() {
		$filters = $this()->{HasGridListFilters::relationship_name()}();

		return $filters->column('ID');
	}

	/**
	 * Provide pages which have
	 *
	 * @return \DataList
	 */
	public function provideGridListItems($parameters = []) {
		if ($this()->{self::SingleFieldName}) {
			if ($this()->hasExtension(HasGridListFilters::class_name())) {

				$filterIDs = $this->filterIDs();

				// name of the field on Pages
				$filterField = HasGridListFilters::relationship_name('ID');

				$pages = \Page::get()->filter([
					$filterField => $filterIDs
				]);

				// debug help
				$count = $pages->count();
				return $pages;
			}
		}
	}
}