<?php
namespace Modular\Fields;
/**
 * Provides editable filters and extension via provideGridListFilters mechanism that in-page gridlist can use.
 *
 * @package Modular\Fields
 */
class GridListFilters extends Field {
	const RelationshipName = 'GridListFilters';
	const RelatedClassName = 'Modular\Models\GridListFilter';

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