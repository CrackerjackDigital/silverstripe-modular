<?php
namespace Modular\GridList\Interfaces;

interface ItemsConstraints {
	/**
	 * @param \SS_LIst $items
	 * @param          $filters
	 * @param array    $parameters
	 * @return void
	 */
	public function constrainGridListItems(&$items, $filters, $parameters = []);
}
