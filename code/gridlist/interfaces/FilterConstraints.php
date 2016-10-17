<?php
namespace Modular\GridList\Interfaces;

interface FilterConstraints {
	public function constrainGridListFilters($items, &$filters);
}