<?php
namespace Modular\Extensions\Model;
use Modular\ModelExtension;

/**
 * Model extension which tracks if a record has been updated since the last build, e.g. by a User interaction or other
 * update. This way we can check on build if BuildModelUnchanged = false then don't mess with the record
 * and conversely if BuildModelUnchanged then we can mess with it.
 */
class Built extends ModelExtension {
    const DateFieldName = 'BuiltModelLastBuild';
    const FlagFieldName = 'BuiltModelChanged';
    const ChangedValue = true;

    private static $db = [
        self::DateFieldName => 'SS_DateTime',
        self::FlagFieldName => 'Boolean'
    ];
    /**
     * Add controller names as matched to Controller::curr() if you want to disable
     * this extension while a particular controller is current.
     * @var array
     */
    private static $disable_for_controllers = [
        // 'SomeControllerClassName'
    ];

    // track if this extension is enabled
    private static $enabled = true;

	/**
	 * Remove all extension defined fields from the CMS.
	 * @param \FieldList $fields
	 */
    public function updateCMSFields(\FieldList $fields) {
        static::remove_own_fields($fields);
    }

    /**
     * If the extension is disabled then we are probably doing a build so update:
     *
     * -    BuiltModelLastBuild -> now();
     * -    BuildModelChanged -> false
     *
     * Otherwise if enabled then we are running in CMS/other process so update:
     *
     * -    BuildModelChanged -> true
     *
     */
    public function onBeforeWrite() {
        if ($this->enabled()) {
            // enabled so set ChangedFlag to true
            $this->{self::FlagFieldName} = self::ChangedValue;
        } else {
            // disabled so probably build process, update build date to now() and flag to unchanged value.
            $this->{self::FlagFieldName} = !self::ChangedValue;
            $this->{self::DateFieldName} = date('Y-m-d h:i:s');
        }
        parent::onBeforeWrite();
    }

    /**
     * Check if the extended model has changed or not since last build.
     * @return bool
     */
    public function builtModelChanged() {
        return $this()->{self::FlagFieldName} === self::ChangedValue;
    }

    /**
     * Check if extension is enabled or not or if the current controller is in config.disable_for_controllers.
     *
     * @return boolean - true if enabled, false otherwise.
     */
    public static function enabled() {
        // try and short-circuit the array checks etc for speed.
        if ($enabled = \Config::inst()->get(__CLASS__, 'enabled')) {

            if ($controllers = \Config::inst()->get(__CLASS__, 'disable_for_controllers')) {
                if ($currentControllerClassName = \Controller::has_curr()
                    ? \Controller::curr()->class
                    : false
                ) {
                    $enabled = !in_array($currentControllerClassName, $controllers);
                }
            }
        }
        return $enabled;
    }

}