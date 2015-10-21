<?php
class ModularContentControllerExtension extends Extension {
    /**
     * @return Controller
     */
    public function __invoke() {
        return $this->owner;
    }

	public function onBeforeInit() {
		// expect an Application object derived from ModularModule to be configured.
		Injector::inst()->create('Application')->add_requirements(ModularModule::BeforeInit);
	}
	public function onAfterInit() {
		// expect an Application object derived from ModularModule to be configured.
		Injector::inst()->create('Application')->add_requirements(ModularModule::AfterInit);
	}

    public function ActionLink($action) {
        return Controller::join_links(
            $this()->Link(),
            $action
        );
    }
}