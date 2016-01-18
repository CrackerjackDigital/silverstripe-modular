<?php

class RoundRobinMultipleArrayList extends ArrayList {
	/**
	 * Create the list of lists, each item in the $items parameter should itself
	 * be a list.
	 *
	 * @param array $lists
	 */
	public function __construct(array $lists = array()) {
		$multi = new MultipleIterator(MultipleIterator::MIT_NEED_ANY);

		$items = [];
		/** @var SS_List $list */
		foreach ($lists as $list) {
			/** @var ArrayList|DataList $list */
			$list = is_array($list) ? new ArrayList($list) : $list;

			$multi->attachIterator($list->getIterator());
		}
		foreach ($multi as $list) {
			foreach ($list as $item) {
				if ($item) {
					$items[] = $item;
				}
			}
		}
		parent::__construct($items);
	}
}