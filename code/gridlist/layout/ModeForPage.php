<?php
namespace Modular\GridList\Layout;

use Modular\Application;
use Modular\GridList\Interfaces\TempleDataProvider;
use Modular\ModelExtension;

/**
 * Extension to add to a GridList view model to hard-wire a particular GridList view for the current page.
 * Should be added after e.g. Modular\GridList\Fields\Mode field which allows mode selection.
 *
 * @package Modular\GridList\Layout
 */
class ModeForPage extends ModelExtension implements TempleDataProvider {
	/**
	 *
	 * Use the current page's gridlist_default_mode if set otherwise whatever we had before.
	 *
	 * @param array $templateData
	 * @return array [ 'Mode' => mode e.g. 'grid' or 'list' (or empty if none set)
	 */
	public function provideGridListTemplateData($templateData = []) {
		$mode = isset($templateData['Mode']) ? $templateData['Mode'] : '';

		// page may be null if it's a new page
		if ($page = Application::get_current_page()) {
			// set to page mode if set, otherwise keep what we have already
			$mode = $page->config()->get('gridlist_default_mode') ?: $mode;
		}
		return [
			'Mode' => $mode
	    ];
	}
}
