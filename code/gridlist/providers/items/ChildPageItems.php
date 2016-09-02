<?php
namespace Modular\GridList\Providers\Items;

use Modular\GridList\Interfaces\ItemsProvider;
use Modular\ModelExtension;

class ChildPageItems extends ModelExtension implements ItemsProvider {
	use children;
}