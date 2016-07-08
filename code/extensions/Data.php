<?php
use \Modular\Helpers\Strings;

class ModularDataExtension extends DataExtension {
	use Modular\config;

	const DefaultTabName = 'Root.Main';

	private static $enabled = true;

	/**
	 * @return DataObject
	 */
	public function __invoke() {
		return $this->owner;
	}

	/**
	 * Writes the extended model and returns it if write returns truthish, otherwise returns null.
	 *
	 * @return \DataObject|null
	 */
	public function writeAndReturn() {
		if ($this()->write()) {
			return $this();
		}
	}

	/**
	 * Return a configuration setting optionally filtered by filterCallback.
	 *
	 * @param               $name
	 * @param null          $key
	 * @param callable|null $filterCallback suitable for passing to array_filter
	 * @return array|null|string
	 */
	public static function own_config($name, $key = null, Callable $filterCallback = null) {
		$value = ModularModule::get_config_setting(
			$name,
			$key,
			null,
			Config::UNINHERITED
		);
		if (is_array($value) && $filterCallback) {
			return array_filter(
				$value,
				$filterCallback
			);
		}
		return $value;
	}

	public function ownerConfigSetting($name, $key = null, $options = null) {
		$value = $this->owner->config()->get($name);
		if ($key && is_array($value) && array_key_exists($key, $value)) {
			return $value[ $key ];
		}
		return $value;
	}


	/**
	 * Add a control to tab from config.class_tab_names depending on the class name of the extended object.
	 *
	 * @param $fieldName
	 * @return mixed
	 */
	/* UNTESTED
		public function classTabName() {
			$tabName = ModularModule::get_config_setting(get_called_class(), 'tab_name');

			// might have per-class/per-field tab name for the field
			$multipleNames = ModularModule::get_config_setting(
				get_called_class(),
				'class_tab_names'
			) ?: [];

			$ownerClass = get_class($this->owner);

			return ModularModule::detokenise(
				$multipleNames
					? (isset($multipleNames[$ownerClass])
					? $multipleNames[$ownerClass]
					: $tabName)
					: $tabName,
				array(
					'id' => $this()->ID,
					'title' => $this()->Title,
					'class' => get_class(),
					'singular' => $this()->i18n_singular_name(),
					'plural' => $this()->i18n_plural_name()
				)
			);
		}
	*/
	public function fieldTabName($fieldName) {
		$tabName = ModularModule::get_config_setting('tab_name') ?: self::DefaultTabName;

		// might have per-field tab name for the field
		$multipleNames = ModularModule::get_config_setting('field_tab_names') ?: [];

		return Strings::detokenise(
			$multipleNames
				? (isset($multipleNames[ $fieldName ])
				? $multipleNames[ $fieldName ]
				: $tabName)
				: $tabName,
			$this->metaData()
		);
	}

	public function fieldLabel($fieldName, $default = '') {
		$label = ModularModule::get_localised_config_string(
			get_class(),
			$fieldName,
			$default ?: $fieldName,
			$this->metaData()
		);
		return Strings::decamel($label);
	}

	protected function metaData() {
		return array(
			'id'       => $this()->ID,
			'title'    => $this()->Title,
			'class'    => get_class(),
			'singular' => $this()->i18n_singular_name(),
			'plural'   => $this()->i18n_plural_name(),
		);
	}

	public static function enabled() {
		return static::get_config_setting('enabled');
	}

	public static function enable() {
		Config::inst()->update(get_called_class(), 'enabled', true);
	}

	public static function disable() {
		Config::inst()->update(get_called_class(), 'enabled', false);
	}
}
