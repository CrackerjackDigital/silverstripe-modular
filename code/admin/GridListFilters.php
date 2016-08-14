<?php

namespace Modular\Admin;

class GridListFilters extends ModelAdmin {

	private static $menu_title = 'GridListFilters';

	private static $url_segment = 'gridlistfilters';

	private static $managed_models = [
		'Modular\Models\GridListFilter'
	];

}