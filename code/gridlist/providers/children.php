<?php
namespace Modular\GridList\Providers;
/**
 * Trait provides children to the gridlist
 */

trait children {
	abstract public function __invoke();

	public function provideGridListItems() {
		return $this()->Children();
	}
}