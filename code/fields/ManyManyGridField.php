<?php
namespace Modular\Fields;

use Modular\GridField\GridFieldConfig;
use Modular\Relationships\HasManyMany;
use Quaff\Controllers\Model;

use Modular\GridField\GridFieldOrderableRows;

class HasManyManyGridField extends HasManyMany {
	const GridFieldConfigName = 'Modular\GridField\HasManyManyGridFieldConfig';

	// force grid field view
	private static $show_as = self::ShowAsGridField;

	private static $cms_tab_name = '';

	private static $allow_multiple = true;
	private static $allow_create = true;
	private static $allow_sorting = true;

}