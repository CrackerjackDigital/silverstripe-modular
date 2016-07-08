<?php

class ModularContentControllerExtension extends Extension {
	const ApplicationServiceClassName = 'Application';

	/**
	 * @return Controller
	 */
	public function __invoke() {
		return $this->owner;
	}

	public function onBeforeInit() {
		// expect an Application object derived from ModularModule to be configured.
		Injector::inst()->create(static::ApplicationServiceClassName)
			->requirements($this(), ModularModule::BeforeInit);
	}

	public function onAfterInit() {
		// expect an Application object derived from ModularModule to be configured.
		Injector::inst()->create(static::ApplicationServiceClassName)
			->requirements($this(), ModularModule::AfterInit);
	}

	public function ActionLink($action) {
		return Controller::join_links(
			$this()->Link(),
			$action
		);
	}
}