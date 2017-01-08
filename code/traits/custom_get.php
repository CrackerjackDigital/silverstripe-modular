<?php
namespace Modular\Traits;
/**
 * Return a collection class derived from \DataList instead of a \DataList. The class is nominated in the
 * exhibiting classes config.custom_list_class_name and custom_get can be called from the a method overriding
 * the standard SilverStripe get method.
 *
 * @package Modular\Traits
 */
trait custom_get {

	public static function custom_get($callerClass = null, $filter = "", $sort = "", $join = "", $limit = null, $containerClass = 'DataList') {
		$oldClassName = '';
		if ($listClassName = static::custom_list_class_name()) {
			if ($listClassName != $containerClass) {
				$oldClassName = \DataList::getCustomClass('DataList');
				$oldClassName::useCustomClass('DataList', $listClassName);
			}
		}
		// use custom class name if set
		$callerClass = $callerClass ?: static::custom_class_name();

		$list = parent::get($callerClass, $filter, $sort, $join, $limit, $containerClass);

		if ($listClassName != $containerClass && $oldClassName) {
			$oldClassName::useCustomClass('DataList', $oldClassName);
		}
		return $list;
	}

	/**
	 * @return string
	 */
	private static function custom_list_class_name() {
		return \Config::inst()->get(get_called_class(), 'custom_list_class_name');
	}

}