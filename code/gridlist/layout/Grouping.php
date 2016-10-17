<?php
namespace Modular\GridList\Layout;

use Modular\Fields\Field;

class Grouping extends Field {
	const SingleFieldName = 'Grouping';
	const SingleFieldSchema = 'Varchar(32)';

	// map of fields to display names which can be used to group, shown in CMS dropdown
	private static $options = [
		# 'EventDate' => 'Event Date'
	];
	// modes which this grouping applies to (empty if all)
	private static $confine_to_modes = [
		# 'list',
	    # 'grid'
	];

	public function cmsFields() {
		return [
			new \DropdownField(
				static::SingleFieldName,
				null,
				$this->config()->get('confine_to_modes')
			)
		];
	}

	/**
	 * If we are in list mode then group items by event date
	 *
	 * @param \ArrayList|\DataList $items
	 * @param $mode
	 */
	public function layoutGridListItems(&$items, $mode) {
		if ($groupField = $this()->{static::SingleFieldName}) {
			$modes = $this->config()->get('confine_to_modes') ?: [];
			if (isset($modes[$mode]) || !$modes) {
				$items = \GroupedList::create($items->Sort($groupField));
			}
		}
	}
}