<?php
namespace Modular\Relationships;

use Modular\Fields\Field;
use Modular\Fields\ModelTag;
use Modular\Model;
use Modular\Models\GridListFilter;
use Modular\Module;

/**
 * RelatedPages
 *
 * @package Modular\Relationships
 * @method RelatedPages
 */
abstract class RelatedPages extends HasManyMany {
	private static $multiple_select = true;

	private static $cms_tab_name = 'Root.Relationships';

	private static $sortable = false;

	/**
	 * Add tag field for this relationship's pages
	 * @return array
	 */
	public function cmsFields() {
		$multipleSelect = (bool) $this->config()->get('multiple_select');
		$relatedClassName = static::RelatedClassName;

		return [
			(new \TagField(
				static::RelationshipName,
				null,
				$relatedClassName::get()
			))->setIsMultiple($multipleSelect)->setCanCreate(false),
		];
	}

	/**
	 * Add this exensions related pages to items, keyed by the Relationship Name
	 * @param array $items
	 */
	public function provideGridListItems(array &$items) {
		$key = static::relationship_name();

		$items = $this()->{static::relationship_name()}();

		$items[$key] = array_merge(
			isset($items[$key]) ? $items[$key] : [],
			$items
		);
	}

	/**
	 * Add this relationships related pages to the grid list items in the corresponding filter group as a data list.
	 * ie $groupedItems will be built so:
	 * [
	 *      'people' => DataList of related pages with the 'people' filter tag,
	 *      'news' => DataList of related pages with the 'news' filter tag
	 * ]
	 *
	 * @param array $groupedItems
	 */
	public function provideFilteredGridListItems(array &$groupedItems) {
		$filters = GridListFilter::get();
		/** @var ModelTag|Model $filter */
		$allItems = $this()->{static::RelationshipName}();

		foreach ($filters as $filter) {
			$filtered = $allItems->filter([
				'GridListFilters.ID' => $filter->ID
			]);
			if (isset($groupedItems[ $filter->ModelTag() ])) {
				$groupedItems[ $filter->ModelTag() ]->merge($filtered);
			} else {
				$groupedItems[ $filter->ModelTag() ] = $filtered;
			}
		}
	}

}