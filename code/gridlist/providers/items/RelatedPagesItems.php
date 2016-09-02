<?php
namespace Modular\GridList\Providers\Items;

use Modular\GridList\Interfaces\ItemsProvider;
use Modular\ModelExtension;

class RelatedPagesItems extends ModelExtension implements ItemsProvider {
	use related;
}