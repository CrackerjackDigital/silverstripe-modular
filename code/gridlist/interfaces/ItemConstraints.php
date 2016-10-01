<?php
namespace Modular\GridList\Interfaces;

interface ItemConstraints {
	/**
	 * @param \SS_LIst $items
	 * @return mixed
	 */
	public function constrainGridListItems(&$items);
}