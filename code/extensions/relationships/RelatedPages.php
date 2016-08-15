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
class RelatedPages extends Field {
	const RelatedClassName = '';
	const RelationshipName = '';

	private static $multiple_select = true;

	private static $cms_tab_name = 'Root.Relationships';

	public function extraStatics($class = null, $extension = null) {
		$parent = parent::extraStatics($class, $extension) ?: [];

		return array_merge_recursive(
			$parent,
			[
				'many_many' => [
					static::RelationshipName => static::RelatedClassName,
				],
			]
		);
	}

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
	 * Add this relationships related pages to the grid list items in the corresponding filter group as a data list.
	 * ie $groupedItems will be built so:
	 * [
	 *      'people' => DataList of related pages with the 'people' filter tag,
	 *      'news' => DataList of related pages with the 'news' filter tag
	 * ]
	 *
	 * @param array $groupedItems
	 */
	public function provideGridListItems(array &$groupedItems) {
		$filters = GridListFilter::get();
		/** @var ModelTag|Model $filter */
		$items = $this->$this()->{static::RelationshipName}();

		foreach ($filters as $filter) {
			$filtered = $items->filter([
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