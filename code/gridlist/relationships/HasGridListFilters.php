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
	 * Return filters related to the extended model
	 *
	 * #return \SS_List
	 */
	public function provideGridListFilters() {
		return $this->related();
	}
}