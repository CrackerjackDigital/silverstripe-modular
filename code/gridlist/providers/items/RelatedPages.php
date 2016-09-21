<?php
namespace Modular\GridList\Providers\Items;

use Modular\Fields\Field;
use Modular\GridList\Interfaces\ItemsProvider;

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
		if ($this()->{static::SingleFieldName}) {
			$classes = \ClassInfo::subclassesFor(RelatedPages::class_name());

			$items = new \ArrayList();

			foreach ($classes as $class) {
				if ($class !== RelatedPages::class_name()) {
					if ($this()->hasExtension($class)) {
						$items->merge(
							$this()->{$class::relationship_name()}()
						);
					}
				}
			}
			return $items;
		}
	}

}