<?php
namespace Modular;

use \DataExtension;

class ModelExtension extends DataExtension {
	use config;
	use enabler;

	const DefaultTabName = 'Root.Main';

	private static $enabled = true;

	private static $cms_tab_name = '';

	/**
	 * @return Model
	 */
	public function __invoke() {
		return $this->owner();
	}

	/**
	 * Typehint doesn't work for __invoke in PhpStorm so while coding use this method...
	 *
	 * @return Model
	 */
	public function owner() {
		return $this->owner;
	}

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
