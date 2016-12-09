<?php
namespace Modular\GridList\Layout;

use Modular\Application;
use Modular\Fields\Field;
use Modular\GridList\Constraints;
use Modular\GridList\GridList;
use Modular\GridList\Interfaces\GridListTempleDataProvider;

/**
 * Return layout GridListColumns to template so can be applied e.g. to bootstrap col-md-{$GridListColumns}
 */
class PageLength extends Field implements GridListTempleDataProvider {
	const SingleFieldName   = 'PageLength';
	const SingleFieldSchema = 'Int';

	// default column count for each item (so not the 'width' that shows in the dropdown).
	private static $default_page_length = 12;

	// map actual page length returned to what is available in CMS incase different in implementation to how it looks.
	private static $length_map = [
		'grid' => [
			12 => 12,
			18 => 18,
			24 => 24,
		],
	    'list' => [
	    	3 => 3,
	        6 => 6,
	        9 => 9
	    ]
	];

	/**
	 * Add an option set with values tuned to the default mode of the page, e.g. 'grid' or 'list'. This is usefull when
	 * a list mode needs to show more items, or if list mode does grouping of items in which case the page length is
	 * the number of groups to show, not the number of items.
	 *
	 * @return array
	 */
	public function cmsFields() {
		$mode = (string)\Config::inst()->get('Modular\GridList\GridList', 'default_mode');

		if ($page = Application::get_current_page()) {
			$mode = (string)$page->config()->get('gridlist_default_mode') ?: $mode;
		}
		$lengthMap = $this->config()->get('length_map');

		if (isset($lengthMap[$mode])) {
			// use the mode as key to the map
			$lengthMap = $lengthMap[$mode];
		} elseif (!is_numeric(key($lengthMap))) {
			// we have two levels, use the first as the map
			$lengthMap = current($lengthMap);
		}
		return array_merge(
			parent::cmsFields(),
			[
				static::SingleFieldName => new \OptionsetField(
					static::SingleFieldName,
					'',
					$lengthMap,
					$this()->{static::SingleFieldName} ?: $this->defaultPageLength()
				),
			]
		);
	}

	/**
	 * Provide the 'GridListColumnWidth' field to the GridList template data as 'limit' and 'PageLength' (they may be different eventually)
	 *
	 * @param array $existingData
	 * @return array
	 */
	public function provideGridListTemplateData($existingData = []) {
		$length = ($this()->{static::SingleFieldName} ?: $this->defaultPageLength());
		return [
			Constraints::PageLengthGetVar => $length,
			self::SingleFieldName         => $length,
		];
	}

	/**
	 * Returns default page length, which may not be the CMS UI length but the 'real' implementation length.
	 *
	 * @return int
	 */
	protected function defaultPageLength() {
		return $this()->config()->get('gridlist_page_length')
			?: $this->config()->get('default_page_length');
	}
}