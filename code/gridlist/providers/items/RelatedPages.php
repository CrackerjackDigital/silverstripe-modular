<?php
namespace Modular\GridList\Providers\Items;

use Modular\GridList\Interfaces\ItemsProvider;
use Modular\ModelExtension;

class RelatedPages extends ModelExtension implements ItemsProvider {
	/**
	 * Use the 'related' method to return related pages.
	 *
	 * @return mixed
	 */
	public function provideGridListItems() {
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