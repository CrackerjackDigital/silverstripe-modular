<?php
namespace Modular\Models;
/**
 * @property string ModelTag
 */
use Modular\Model;

class GridListFilter extends Model {
	private static $db = [
		'Sort' => 'Int'
	];

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->replaceField('Sort', new \ReadonlyField('Sort', 'Sort order'));
		return $fields;

	}

	/**
	 * GridListFilter should have ModelTag extension so use that as the Filter value in page etc.
	 * @return string
	 */
	public function Filter() {
		return $this->ModelTag;
	}

	public function FilterLink() {
		return \Director::get_current_page()->Link() . '?filter=' . $this->Filter();
	}
}