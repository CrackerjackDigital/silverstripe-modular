<?php

namespace Modular\Models;

/**
 * @property string ModelTag
 */
use Modular\Fields\ModelTag;
use Modular\Fields\Title;

class GridListFilter extends \Modular\Model {
	const TagFieldName  = ModelTag::SingleFieldName;
	const SortFieldName = 'GridListFilterSort';

	private static $db = [
		self::SortFieldName => 'Int',
	];

	private static $default_sort = 'GridListFilterSort DESC, Title ASC';

	private static $summary_fields = [ Title::SingleFieldName, self::TagFieldName ];

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->replaceField( self::SortFieldName, new \ReadonlyField( self::SortFieldName, 'Sort order' ) );

		return $fields;

	}

	/**
	 * Return the filter tag from the extended model.
	 *
	 * @return string
	 */
	public function Filter() {
		return $this()->{static::TagFieldName};
	}

	public function FilterLink() {
		return \Director::get_current_page()->Link() . '?filter=' . $this->Filter();
	}
}