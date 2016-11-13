<?php
namespace Modular\GridList\Interfaces;

interface ItemHandler {
	/**
	 * Do something with grid list output before rendering the page.
	 * @param $items
	 * @param $filters
	 * @param $parameters
	 */
	public function handleGridListItems($items, $filters, $parameters);
}
