<?php
namespace Modular\GridList\Providers\Items;

use Modular\Fields\Field;
use Modular\GridList\Interfaces\ItemsProvider;
use Modular\Relationships\HasRelatedPages;

/**
 * Provide all pages which have been related to this page via the 'RelatedPages' tab.
 *
 * @package Modular\GridList\Providers\Items
 */
class RelatedPages extends Field implements ItemsProvider {
	const SingleFieldName   = 'ProvideRelatedPages';
	const SingleFieldSchema = 'Boolean';

	private static $defaults = [
		self::SingleFieldName => true
	];

	/**
	 * Use the 'related' method to return related pages.
	 *
	 * @return mixed
	 */
	public function provideGridListItems() {
		if ($this()->{self::SingleFieldName}) {
			if ($page = \Director::get_current_page()) {
				$items = new \ArrayList();

				// iterate through children of 'HasRelatedPages', eg 'BusinessPages', 'DivisionPages' etc
				foreach (\ClassInfo::subclassesFor(HasRelatedPages::class_name()) as $className) {
					if ($className == HasRelatedPages::class_name()) {
						// skip the related pages class itself
						continue;
					}

					if ($page->hasExtension($className)) {
						// get all the related e.g. country pages to this page via the 'RelatedCountries' back relationship
						$items->merge(
							$page->{$className::relationship_name()}()
						);
					}
				}
				return $items;
			}
		}
	}

}