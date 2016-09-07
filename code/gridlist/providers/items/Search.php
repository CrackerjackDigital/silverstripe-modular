<?php
namespace Modular\GridList\Providers\Items;

use Modular\GridList\Interfaces\ItemsProvider;
use Modular\ModelExtension;

class Search extends ModelExtension implements ItemsProvider {
	public function provideGridListItems() {
		return \Injector::inst()->get('SearchService')->items();
	}
}