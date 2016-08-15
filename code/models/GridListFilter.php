<?php
namespace Modular\Models;
/**
 * @property string ModelTag
 */
use Modular\Model;

class GridListFilter extends Model {
	/**
	 * GridListFilter should have ModelTag extension so use that as the Filter value in page etc.
	 * @return string
	 */
	public function Filter() {
		return $this->ModelTag;
	}
}