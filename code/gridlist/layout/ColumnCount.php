<?php
namespace Modular\GridList\Layout;
/**
 * Return layout GridListColumns to template so can be applied e.g. to bootstrap col-md-{$GridListColumns}
 */
class ColumnCount extends \Modular\ModelExtension {
	private static $class_to_column_count = [];
	private static $default_column_count = 4;

	// map number of columns to individual column widths for .col-md-x in bootstrap
	private static $width_map = [
		1 => 12,
		2 => 6,
		3 => 4,
	    4 => 3,
	    6 => 2,
	    12 => 1
	];

	public function GridListColumns() {
		$map = $this->config()->get('class_to_column_count');
		$pageClassName = \Director::get_current_page()->ClassName;

		if (array_key_exists($pageClassName, $map)) {
			$count = $map[$pageClassName];
		} else {
			$count = (int)$this->config()->get('default_column_count');
		}

		$widthMap = $this->config()->get('width_map');
		if (isset($widthMap[$count])) {
			return $widthMap[$count];
		} else {
			return $widthMap[(int)$this->config()->get('default_column_count')];
		}
	}
}