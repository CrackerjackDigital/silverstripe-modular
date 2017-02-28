<?php
namespace Modular\Extensions\Controller;

use Injector;
use Modular\Traits\debugging;
use Modular\Module;
use Modular\Traits\owned;

/**
 * Add to application ContentControllers to get Modular functionality such as requirements.
 *
 * @package Modular
 */
class Content extends \Extension {
	use owned;

	public function ActionLink($action) {
		return $this()->join_links(
			$this()->Link(),
			$action
		);
	}
}
