<?php
namespace Modular\GridList\Providers;
/**
 * Trait provides related models to the gridlist, e.g. via a RelatedPages derived class where this trait has been added.
 */

trait related {
	abstract public function __invoke();

	abstract public function related();

	/**
	 * Use the 'related' method to return related pages.
	 * @return mixed
	 */
	public function provideGridListItems() {
		return $this()->related();
	}
}