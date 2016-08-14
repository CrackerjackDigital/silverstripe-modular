<?php
namespace Modular\Admin;

class TagAdmin extends ModelAdmin {
	private static $menu_title = 'Tags';

	private static $url_segment = 'tags';

	private static $managed_models = [
		'Modular\Models\Tag'
	];

}