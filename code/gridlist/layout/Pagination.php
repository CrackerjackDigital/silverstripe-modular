<?php
namespace Modular\GridList\Layout;

use Modular\Fields\Field;
use Modular\GridList\GridList;
use Modular\GridList\Interfaces\GridListTempleDataProvider;

/**
 * Return layout GridListColumns to template so can be applied e.g. to bootstrap col-md-{$GridListColumns}
 */
class Pagination extends Field implements GridListTempleDataProvider {
	const SingleFieldName   = 'PageLength';
	const SingleFieldSchema = 'Int';

	// default column count for each item (so not the 'width' that shows in the dropdown).
	private static $default_page_length = 12;

	// map actual page length returned to what is available in CMS incase different in implementation to how it looks.
	private static $length_map = [
		12 => 12,
		18 => 18,
		24 => 24,
	];

	public function cmsFields() {
		return array_merge(
			parent::cmsFields(),
			[
				static::SingleFieldName => new \OptionsetField(
					static::SingleFieldName,
					'',
					$this->config()->get('length_map'),
					$this()->{static::SingleFieldName} ?: $this->defaultPageLength()
				),
			]
		);
	}

	/**
	 * Provide the 'GridListColumnWidth' field to the GridList template data
	 *
	 * @param array $existingData
	 * @return array
	 */
	public function provideGridListTemplateData($existingData = []) {
		$start = GridList::service()->Filters()->start();
		$limit = ($this()->{static::SingleFieldName}
			?: (GridList::service()->Filters()->limit()
				?: $this->defaultPageLength()));

		return [
			'Start'               => $start,
			self::SingleFieldName => $limit
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