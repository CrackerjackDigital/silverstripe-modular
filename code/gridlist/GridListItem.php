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
		$filterTags = '';

		if ($this()->hasExtension(HasGridListFilters::class_name())) {
			$filterTags = $this->FilterTags();
		}
		$template = $this->template();

		return $this()->renderWith($template, new \ArrayData([
			'Columns'    => $columns,
			'FilterTags' => $filterTags,
			'Hash'       => md5($this()->ClassName . $this()->ID),
		]));
	}

	public function FilterTags() {
		return implode(' ', array_merge(
			$this()->{HasGridListFilters::relationship_name()}()->column('ModelTag'),
			$this()->hasMethod('customFilterTags') ? $this()->customFilterTags() : []
		));
	}

	/**
	 * Returns a template name either from extended models config.gridlist_template of set or from the extended models ClassName. In either case the current
	 * GridList view mode (grid, list or search) is appended.
	 *
	 * @return string
	 */
	protected function template() {
		$mode = GridList::service()->mode();

		if (!$template = $this()->config()->get('gridlist_template')) {
			$template = "GridList/" . $this()->ClassName;
		}
		return "$template" . '_' . $mode;
	}
}