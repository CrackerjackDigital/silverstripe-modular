<?php
namespace Modular\GridField;

use \GridFieldConfig_RelationEditor;

class GridFieldConfig extends GridFieldConfig_RelationEditor {
	private static $items_per_page = 20;

	public function __construct($itemsPerPage = null) {
		return parent::__construct($itemsPerPage ?: static::config()->get('items_per_page'));
	}
	public static function base() {
		return parent::class;
	}
}