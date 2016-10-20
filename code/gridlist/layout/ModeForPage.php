<?php
namespace Modular\GridList\Layout;

use Modular\Application;
use Modular\GridList\Interfaces\GridListTempleDataProvider;
use Modular\ModelExtension;

/**
 * Extension to add to a GridList view model to hard-wire a particular GridList view for the current page.
 * Should be added after e.g. Modular\GridList\Fields\Mode field which allows mode selection.
 *
 * @package Modular\GridList\Layout
 */
class ModeForPage extends ModelExtension implements GridListTempleDataProvider {
	/**
	 * @return array [ 'Mode' => mode e.g. 'grid' or 'list' (or empty if none set)
	 */
	public function provideGridListTemplateData($existingData = []) {
		$mode = '';

		// page may be null if it's a new page
		if ($page = Application::get_current_page()) {
			$mode = $page->config()->get('gridlist_default_mode') ?: '';
		}
		return [
			'Mode' => $mode
	    ];
	}
}
