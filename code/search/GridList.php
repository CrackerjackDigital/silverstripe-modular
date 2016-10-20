<?php
namespace Modular\Search;
/**
 * Add some search specific functionality to the standard gridlist.
 *
 * @package Modular\Search
 */
class GridList extends \Modular\GridList\GridList {
	public function SearchTerms() {
		return Service::terms();
	}
}