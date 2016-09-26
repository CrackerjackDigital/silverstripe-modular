<?php
namespace Modular\GridList\Layout;

use Modular\Fields\Field;
use Modular\GridList\Interfaces\GridListTempleDataProvider;

/**
 * Return layout GridListColumns to template so can be applied e.g. to bootstrap col-md-{$GridListColumns}
 */
class ColumnWidth extends Field implements GridListTempleDataProvider {
	const SingleFieldName   = 'ColumnWidth';
	const SingleFieldSchema = 'Int';

	// default column count for each item (so not the 'width' that shows in the dropdown).
	private static $default_column_count = 4;

	// map number of columns to column width for .col-md-x in bootstrap this is for 12 column layout
	private static $width_map = [
		# 1 => 12,
		# 2 => 6,
		3 => 4,
		4 => 3,
		# 6 => 2,
		# 12 => 1
	];

	public function cmsFields() {
		return array_merge(
			parent::cmsFields(),
			[
				static::SingleFieldName => new \OptionsetField(
					static::SingleFieldName,
					'',
					$this->config()->get('width_map'),
					$this()->{static::SingleFieldName} ?: $this->defaultColumnCount()
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
		return [
			self::SingleFieldName => ($this()->{static::SingleFieldName} ?: $this->defaultColumnCount())
		];
	}

	/**
	 * Returns default column count, which is not the 'width' set in the cms, but the on-page column count for this column
	 * @return int
	 */
	protected function defaultColumnCount() {
		return $this()->config()->get('gridlist_column_count')
			?: $this->config()->get('default_column_count');
	}
}