<?php
namespace Modular\GridList\Interfaces;

interface ItemsConstraints {
	/**
	 * @param \SS_List $items
	 * @return mixed
	 */
	public function constrainGridListItems(&$items);
}