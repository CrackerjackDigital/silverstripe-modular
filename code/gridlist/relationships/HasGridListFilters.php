<?php
namespace Modular\Relationships;
/**
 * Provides a tag field where filters can be added.
 *
 * @package Modular\Fields
 */
class HasGridListFilters extends HasManyMany {
	const RelationshipName = 'GridListFilters';
	const RelatedClassName = 'Modular\Models\GridListFilter';

	private static $sortable = false;

	public function cmsFields() {
		return [
			new \TagField(
				static::RelationshipName,
				'',
				\DataObject::get(static::RelatedClassName)
			),
		];
	}

	/**
	 * Add filters from this fields RelatedClassName
	 *
	 * @param \ArrayList $filters
	 */
	public function provideGridListFilters(\ArrayList $filters) {
		$filters->merge(
			\DataObject::get(static::RelatedClassName)->sort('Title')
		);
	}
}