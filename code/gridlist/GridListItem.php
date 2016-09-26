<?php
namespace Modular\GridList;

use Modular\ModelExtension;
use Modular\Relationships\HasGridListFilters;

/**
 * Render the extended model (e.g. Block) or view (e.g. Page) as an item in a gridlist using the associated template
 */
class GridListItem extends ModelExtension {
	/**
	 * Renders the item into the gridlist with selected template passing through Filters which are defined on the item.
	 * GridListItem constructor.
	 */
	public function GridListItem($columns = 0) {
		$filters = [];

		if ($this()->hasExtension(HasGridListFilters::class_name())) {
			$filters = $this()->{HasGridListFilters::relationship_name()}();
		}
		return $this()->renderWith($this->template(), new \ArrayData([
			'Columns' => $columns,
			'Filters' => $filters
		]));
	}

	/**
	 * Returns a template name either from extended models config.gridlist_template of set or from the extended models ClassName. In either case the current
	 * GridList view mode (grid, list or search) is appended.
	 *
	 * @return string
	 */
	protected function template() {
		if (!$template = $this()->config()->get('gridlist_template')) {
			$template = "GridList/" . $this()->ClassName;
		}
		return "$template" . '_' . \Injector::inst()->get('GridListFilterService')->mode();
	}
}