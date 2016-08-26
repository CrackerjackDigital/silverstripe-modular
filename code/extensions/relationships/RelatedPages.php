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
abstract class RelatedPages extends HasManyMany {
	private static $multiple_select = true;

	private static $cms_tab_name = 'Root.Relationships';

	private static $sortable = false;

	/**
	 * Return related pages for this site, optionally filtered by the associated filter passed as a getVar
	 * @param boolean $filter
	 */
	public function provideGridListItems($filter = false) {
		$relationship = static::relationship_name();

		$pages = $this()->$relationship();

		if ($this->sortable()) {
			$pages->sort(\Modular\GridField\GridField::GridFieldOrderableRowsFieldName);
		}

		return $pages->toArray();
	}
}