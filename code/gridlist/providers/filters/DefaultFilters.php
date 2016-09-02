<?php
namespace Modular\GridList\Providers\Filters;

use Modular\GridList\Interfaces\FiltersProvider;
use Modular\ModelExtension;

class DefaultFilters extends ModelExtension implements FiltersProvider {
	use defaults;
}