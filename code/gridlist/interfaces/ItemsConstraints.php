<?php
namespace Modular\GridList\Interfaces;

interface ItemsConstraints {
	/**
	 * @param \SS_LIst $items
	 * @return mixed
	 */
	public function constrainGridListItems(&$items);
}