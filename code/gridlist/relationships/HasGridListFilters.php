<?php
namespace Modular\Relationships;
use Modular\Fields\Title;

/**
 * Provides a tag field where filters can be added.
 *
 * @package Modular\Fields
 * @method \SS_List GridListFilters()
 */
class HasGridListFilters extends HasManyMany {
	const RelationshipName = 'GridListFilters';
	const RelatedClassName = 'Modular\Models\GridListFilter';

	private static $sortable = false;

	private static $summary_fields = [
		'DisplayGridListFilters' => 'Filters'
	];

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
	 * Return csv list of filters suitable for use eg in summary_fields.
	 * @return string
	 */
	public function DisplayGridListFilters() {
		return implode(', ', $this->related()->column(Title::SingleFieldName));
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