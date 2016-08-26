<?php
namespace Modular\Relationships;
/**
 * Provides a tag field where multiple filters can be added. Filters are shown in page and used to constrain what current records in the GridList are visible.
 *
 * @package Modular\Fields
 */
class HasGridListFilters extends HasManyMany {
	const RelationshipName = 'GridListFilters';
	const RelatedClassName = 'Modular\Models\GridListFilter';

	private static $show_as = self::ShowAsTagsField;

	private static $sortable = false;

	private static $multiple_select = true;

	/**
	 * Add filters from this fields extended class, so for a page it would be filters added to that page.
	 *
	 * @return array map of ID => Title
	 */
	public function provideGridListFilters() {
		return \DataObject::get(static::related_class_name())->sort('Title')->map()->toArray();
	}

	public function provideGridListConstraints() {
		return [
			static::relationship_name('.ID') => array_keys($this->provideGridListFilters())
		];
	}
}