<?php
class CrackerJackControllerExtension extends DataExtension {
    const ActionPrefix = '';

    private static $action_prefix = '';
    /**
     * @return Controller
     */
    public function __invoke() {
        return $this->owner;
    }

    public function ActionLink($action) {
        return Controller::join_links(
            $this()->Link(),
//            CrackerjackModule::get_config_setting(get_called_class(), 'action_prefix') ?: static::ActionPrefix,
            $action
        );
    }
}