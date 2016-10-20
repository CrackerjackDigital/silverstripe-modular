<?php
namespace Modular;
/**
 * owned, has an owner and invoking this object will invoke the call on the owner, e.g. $this()->doSomething() will call $this->owner()->doSomething()
 *
 * @package Modular
 * @property \Object $owner
 */
trait owned {
	public function __invoke() {
		return $this->owner();
	}

	/**
	 * @return Model|\DataObject
	 */
	public function owner() {
		return $this->owner;
	}

	/**
	 * Return the current page (being viewed in the frontend or from CMSMain in the CMS)
	 * @return \DataObject|\SiteTree
	 */
	public function currentPage() {
		if ($page = \Director::get_current_page()) {
			if ($page instanceof \CMSMain) {
				$page = $page->currentPage();
			}
		}
		return $page;
	}
}