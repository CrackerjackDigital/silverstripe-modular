<?php
namespace Modular\GridList\Providers\Items;

use Modular\GridList\Interfaces\ItemsProvider;
use Modular\ModelExtension;

class ChildPages extends ModelExtension implements ItemsProvider {
	public function provideGridListItems() {
		return $this()->Children();
	}

}