<?php
namespace Modular\Extensions\Controller;

use Extension;
use Modular\Module;
use Modular\owned;

class Requirements extends Extension {
	use owned;

	const ApplicationServiceClassName = 'Application';

	public function onBeforeInit() {
		// expect an Application object derived from ModularModule to be configured.
		\Injector::inst()->create(static::ApplicationServiceClassName)
			->requirements(Module::BeforeInit);
	}

	public function onAfterInit() {
		// expect an Application object derived from ModularModule to be configured.
		\Injector::inst()->create(static::ApplicationServiceClassName)
			->requirements(Module::AfterInit);
	}

}