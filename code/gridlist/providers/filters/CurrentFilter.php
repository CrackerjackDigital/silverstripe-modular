<?php
namespace Modular\GridList\Providers\Filters;

use Modular\GridList\GridList;
use Modular\GridList\Interfaces\GridListTempleDataProvider;
use Modular\Search\ModelExtension;

/**
 * CurrentFilter
 *
 * @package Modular\GridList\Providers\Filters
 */
class CurrentFilter extends ModelExtension implements GridListTempleDataProvider {
	const TemplateDataKey = 'Filter';
	/**
	 * Provides the currently selected filter (e.g. from query string parameter). This may be empty.
	 *
	 * @param array $existingData already being provided, immutable
	 * @return array to add to the template data (may be empty if no filter). e.g. [ 'Filter' => 'latest' ] or [ 'Filter' => null ]
	 */
	public function provideGridListTemplateData($existingData = []) {
		$filter = GridList::service()->filter();
		return [ self::TemplateDataKey => $filter ];
	}
}