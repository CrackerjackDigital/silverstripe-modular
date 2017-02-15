<?php
namespace Modular;

use Extension;
use Injector;

/**
 * Add to application ContentControllers to get Modular functionality such as requirements.
 *
 * @package Modular
 */
class ContentControllerExtension extends Extension {
	use owned;
	use debugging;

	public function ActionLink($action) {
		return Controller::join_links(
			$this()->Link(),
			$action
		);
	}
}