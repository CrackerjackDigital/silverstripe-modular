<?php
namespace Modular\Traits;

use Modular\Helpers\Strings;

require_once 'config.php';

trait lang {
	abstract public function __invoke();

	function lang($key, $default = '', array $tokens = []) {
		$calledClass = get_called_class();
		return _t($calledClass, ".$key", $default ?: $calledClass, $tokens);
	}

	/**
	 * Load message from lang.yml for the extension class (e.g. ImageField) and then the extended class (e.g. FullWidthImageBlock) which may override.
	 *
	 * If not found in lang file use the default.
	 *
	 * Replaces tokens in message as usual merging in some default tokens, such as singular and plural names
	 * and the field Title as 'label'.
	 *
	 * @param string $fieldName  - name of field as it is on the form, e.g. 'Title', 'ImageID'
	 * @param string $decoration - what decoration for the field, e.g. 'Title', 'Placeholder', 'Guide'
	 * @param string $default
	 * @param array  $tokens
	 * @param null   $field
	 * @return string
	 */
	public function fieldDecoration($fieldName, $decoration = 'Label', $default = '', array $tokens = [], $field = null) {
		$extraTokens = $this->fieldDecorationTokens();

		$tokens = array_merge(
			$extraTokens,
			( $field instanceof \FormField )
				? [ 'label' => $field->Title() ]
				: [],
			$tokens
		);
		// strip ID suffix if there
		$fieldName = substr($fieldName, -2, 2) == 'ID'
			? substr($fieldName, 0, -2)
			: $fieldName;

		$extensionClass = $this->class;
		$modelClass = $this()->class;

		// we want a model supplied value in preference to an extension supplied value in preference to the default.
		// for has-ones the field name may have an 'ID' appended.
		if (!$value = _t("$modelClass.$fieldName.$decoration", '', $tokens)) {
			// try again with 'ID'
			if (!$value = _t("$modelClass.$fieldName.{$decoration}ID", '', $tokens)) {
				// now try the extension
				if (!$value = _t("$extensionClass.$fieldName.$decoration", '', $tokens)) {
					// try again extension with 'ID'
					if (!$value = _t("$extensionClass.$fieldName.{$decoration}ID", '', $tokens)) {
						// no lang strings found, return detokenised default
						$value = Strings::detokenise($default, $tokens);
					}
				}
			}
		}
		return $value;
	}

	/**
	 * Override to provide specific tokens for replacement in field decorations
	 * as [ 'token-name' => value ]
	 * will be filled into decoration text placeholder {token-name}
	 *
	 * @return array
	 *
	 */
	protected function fieldDecorationTokens() {
		/** @var \DataObject $model */
		$model = $this();
		$single = $this->singularName();
		$plural = $this->pluralName();

		return [
			'singlename' => $single,
		    'pluralname' => $plural,
		    'singular' => $single,
		    'plural' => $plural,
		    'title' => $model ? $model->Title : '',
		    'id' => $model->isInDB() ? $model->ID : _t('Global.New', 'new')
		];
	}

	protected function singularName() {
		if ($this() instanceof \DataObject) {
			$singular = $this()->i18n_singular_name() ?: $this()->singular_name();
		} else {
			$singular = $this->lang('SINGULARNAME');
		}
		return $singular;
	}

	protected function pluralName() {
		if ($this() instanceof \DataObject) {
			$plural = $this()->i18n_plural_name() ?: $this()->plural_name();
		} else {
			$plural = $this->lang('PLURALNAME');
		}
		return $plural;
	}

	/**
	 * Return a string from siteConfig.{$source$name} tokeised with $data, otherwise pass through to
	 * get_localised_config_string to look in lang file and config.
	 *
	 * @param       $source
	 * @param       $name
	 * @param       $default
	 * @param array $data
	 * @param null  $configOptions
	 * @return string
	 */
	public static function get_site_localised_config_setting($source, $name, $default, array $data = [], $configOptions = null) {
		if ($value = \SiteConfig::current_site_config()->{"$source$name"}) {
			return _t($value, $value, $data);
		}
		return self::get_localised_config_string($source, $name, $default, $data, $configOptions);
	}

	/**
	 * Return a string from localised language files or config or default in order of checking existence.
	 *
	 * @param       $source        - classname localised too or config classname
	 * @param       $name          - e.g. fieldname on object or message name in lang
	 * @param       $default       - default to use if not found in lang or config
	 * @param array $data          - data for tokens in resulting string
	 * @param null  $configOptions - options for config, e.g. Config.UNINHERITED
	 * @return string
	 */
	public static function get_localised_config_string($source, $name, $default, array $data = [], $configOptions = null) {
		if ($value = _t("$source.$name", $default, $data)) {
			return $value;
		}

		if ($value = self::config_subsetting($source, strtolower($name), get_called_class(), $configOptions)) {
			if (is_string($value)) {
				return _t($value, $value, $data);
			}
		}
		return _t($default, $default, $data);
	}
}