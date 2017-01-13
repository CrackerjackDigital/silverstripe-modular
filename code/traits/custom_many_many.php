<?php
namespace Modular\Traits;
/**
 * Return a collection class derived from \DataList instead of a \DataList. The class is nominated in the
 * exhibiting classes config.custom_list_class_name and custom_get can be called from the a method overriding
 * the standard SilverStripe get method.
 *
 * @package Modular\Traits
 */
trait custom_many_many {

	/**
	 * Inject call to getCustomManyManyComponents so we get a VersionedManyManyList.
	 *
	 * @param string $componentName
	 * @param null   $filter
	 * @param null   $sort
	 * @param null   $join
	 * @param null   $limit
	 * @return mixed
	 */
	public function getManyManyComponents($componentName, $filter = null, $sort = null, $join = null, $limit = null) {
		return $this->getCustomManyManyComponents($componentName, $filter, $sort, $join, $limit);
	}

	/**
	 * Call this from exhibiting classes getManyManyComponents if required instead.
	 *
	 * @param      $componentName
	 * @param null $filter
	 * @param null $sort
	 * @param null $join
	 * @param null $limit
	 * @return mixed
	 */
	public function getCustomManyManyComponents($componentName, $filter = null, $sort = null, $join = null, $limit = null) {
		$oldClassName = '';
		if ($listClassName = static::custom_many_many_list_class_name()) {
			if ($listClassName != \ManyManyList::class) {
				/** @var string|\Object $oldClassName */
				$oldClassName = \ManyManyList::getCustomClass('ManyManyList');
				$oldClassName::useCustomClass('ManyManyList', $listClassName);
			}
		}

		$list = parent::getManyManyComponents($componentName, $filter, $sort, $join, $limit);

		if ($listClassName != \ManyManyList::class && $oldClassName) {
			$oldClassName::useCustomClass('ManyManyList', $oldClassName);
		}
		return $list;
	}

	/**
	 * @return string custom list class to use if set via config.custom_list_class_name on exhibiting class, should be derived from DataList
	 */
	private static function custom_many_many_list_class_name() {
		return \Config::inst()->get(get_called_class(), 'custom_many_many_list_class_name');
	}

}