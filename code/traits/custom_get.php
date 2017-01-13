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

	/**
	 * VersionedModel lists are Modular\Workflows\VersionedDataList's.
	 *
	 * @param null   $callerClass
	 * @param string $filter
	 * @param string $sort
	 * @param string $join
	 * @param null   $limit
	 * @param string $containerClass
	 * @return mixed
	 */
	public static function get($callerClass = null, $filter = "", $sort = "", $join = "", $limit = null, $containerClass = 'DataList') {
		return static::custom_get($callerClass, $filter, $sort, $join, $limit, $containerClass);
	}

	/**
	 * Call this from exhibiting classes get method if needed instead.
	 *
	 * @param null   $callerClass
	 * @param string $filter
	 * @param string $sort
	 * @param string $join
	 * @param null   $limit
	 * @param string $containerClass
	 * @return mixed
	 */
	public static function custom_get($callerClass = null, $filter = "", $sort = "", $join = "", $limit = null, $containerClass = 'DataList') {
		$oldClassName = '';
		if ($listClassName = static::custom_list_class_name()) {
			if ($listClassName != $containerClass) {
				/** @var string|\Object $oldClassName */
				$oldClassName = \DataList::getCustomClass('DataList');
				$oldClassName::useCustomClass('DataList', $listClassName);
			}
		}
		// use custom class name if set (this is class name of the model which can also be specified, not the list).
		$callerClass = $callerClass ?: static::custom_class_name();

		$list = parent::get($callerClass, $filter, $sort, $join, $limit, $containerClass);

		if ($listClassName != $containerClass && $oldClassName) {
			$oldClassName::useCustomClass('DataList', $oldClassName);
		}
		return $list;
	}

	/**
	 * @return string custom model class to use if set via config.custom_class_name on exhibiting class, should be derived from DataObject
	 */
	private static function custom_class_name() {
		return \Config::inst()->get(get_called_class(), 'custom_class_name');
	}

	/**
	 * @return string custom list class to use if set via config.custom_list_class_name on exhibiting class, should be derived from DataList
	 */
	private static function custom_list_class_name() {
		return \Config::inst()->get(get_called_class(), 'custom_list_class_name');
	}

}