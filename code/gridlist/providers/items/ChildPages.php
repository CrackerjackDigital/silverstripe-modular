<?php
namespace Modular\GridList\Providers\Items;

use Modular\Fields\Field;
use Modular\GridList\Interfaces\ItemsProvider;
use Modular\ModelExtension;

/**
 * Add to a GridList host (e.g. GridListBlock) to provide all children of the page on which the block is added.
 * Adds a field which enables this to be enabled/disabled in CMS.
 *
 * @package Modular\GridList\Providers\Items
 */
class ChildPages extends Field implements ItemsProvider {
	const SingleFieldName = 'ProvideChildren';
	const SingleFieldSchema = 'Boolean';

	private static $defaults = [
		self::SingleFieldName => true
	];

	/**
	 * Return children of the current page
	 * @return \DataList
	 */
	public function provideGridListItems() {
		if ($this()->{static::SingleFieldName}) {
			if ($page = \Director::get_current_page()) {
				return $page->Children();
			}
		}
	}

}