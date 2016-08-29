<?php
namespace Modular\GridList;

use Modular\ModelExtension;

/**
 * Render the extended model (e.g. Block) or view (e.g. Page) as an item in a gridlist using the associated template
 */
class GridListItem extends ModelExtension {
	/**
	 * Renders with selected template passing through parameters
	 * GridListItem constructor.
	 */
	public function GridListItem() {
		return $this()->renderWith($this->template(), func_get_args());
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