<?php
namespace Modular;

use Extension;
use Injector;

class ContentControllerExtension extends Extension {
	use owned;

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
		return Controller::join_links(
			$this()->Link(),
			$action
		);
	}
}