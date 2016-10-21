<?php
namespace Modular\Models;
/**
 * @property string ModelTag
 */
use Modular\Fields\ModelTag;

class GridListFilter extends \Modular\Model {
	const TagFieldName = ModelTag::SingleFieldName;

	private static $db = [
		'Sort' => 'Int'
	];

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->replaceField('Sort', new \ReadonlyField('Sort', 'Sort order'));
		return $fields;

	}

	/**
	 * GridListFilters should have ModelTag extension so use that as the Filter value in page etc.
	 * @return string
	 */
	public function Filter() {
		return $this()->{static::TagFieldName};
	}

	public function FilterLink() {
		return \Director::get_current_page()->Link() . '?filter=' . $this->Filter();
	}
}