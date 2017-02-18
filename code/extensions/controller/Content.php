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
	use debugging;

	const ApplicationServiceClassName = 'Application';

	public function onBeforeInit() {
		// expect an Application object derived from ModularModule to be configured.
		Injector::inst()->create(static::ApplicationServiceClassName)
			->requirements($this(), Module::BeforeInit);
	}

	public function onAfterInit() {
		// expect an Application object derived from ModularModule to be configured.
		Injector::inst()->create(static::ApplicationServiceClassName)
			->requirements($this(), Module::AfterInit);
	}

	public function ActionLink($action) {
		return $this()->join_links(
			$this()->Link(),
			$action
		);
	}
}
