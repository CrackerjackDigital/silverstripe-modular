<?php
namespace Modular;

use Modular\config;
use Modular\enabler;
use Modular\owned;
use \DataExtension;

class ModelExtension extends DataExtension {
	use config;
	use enabler;
	use owned;

	const DefaultTabName = 'Root.Main';

	private static $cms_tab_name = '';

	/**
	 * Writes the extended model and returns it if write returns truthish, otherwise returns null.
	 *
	 * @return Model|null
	 */
	public function writeAndReturn() {
		if ($this()->write()) {
			return $this();
		}
		return null;
	}

	/**
	 * Return the name (path) of the tab in the cms this model's fields should show under from
	 * config.cms_tab_name in:
	 *
	 * this extension or if not set from
	 * the extended model or if not set
	 * then self.DefaultTabName.
	 *
	 * @return string
	 */
	protected function cmsTab() {
		return $this->config()->get('cms_tab_name')
			?: $this()->config()->get('cms_tab_name')
			?: self::DefaultTabName;
	}

}
