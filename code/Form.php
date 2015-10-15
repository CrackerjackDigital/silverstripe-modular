<?php

class ModularForm extends Form
{
	const Good = 'good';
	const Bad  = 'bad';

	const LabelSuffix = 'Label';
	const TabIDPrefix = '';
	const TabIDSuffix = 'Tab';

	private static $show_tab_strip_in_form = false;

	/**
	 * @param      $action
	 * @param      $controller
	 * @param      $name
	 * @param      $fields
	 * @param      $actions
	 * @param null $validator
	 * @return ModularForm
	 */
	public static function create_for_action($action, ContentController $controller, $name, FieldList $fields, FieldList $actions, $validator = null) {
		$formClassName = get_called_class();

		$name = $name ?: $formClassName;

		$fields = $fields ?: new FieldList();
		$actions = $actions ?: new FieldList();
		$validator = $validator ?: new RequiredFields();

		self::add_fields($action, $fields, $validator);

		self::tabify($action, $fields);

		self::add_actions($action, $actions);

		/** @var ModularForm $form */
		$form = new $formClassName($controller, $name, $fields, $actions, $validator);

		$form->setFormAction($controller->Link($action));

		return $form;
	}

	public static function set_message($messageOrLangKey, $messageType, $data = []) {
		Session::setFormMessage(
			static::get_full_name(),
			static::get_form_message($messageOrLangKey, $data),
			$messageType
		);
	}

	/**
	 * @param      $forAction
	 * @param bool $fullLinks
	 * @return string HTML text of tabstrip in a 'ul'
	 */
	public static function tab_strip($forAction, $fullLinks = false) {
		$tabs = ModularModule::get_config_setting(
			get_called_class(),
			'tabs',
			$forAction
		) ?: [];

		$html = '';
		$baseLink = '';

		if ($profiledPage = ProfiledPage::get()->first()) {
			$baseLink = $profiledPage->Link();
		}

		if ($tabs) {
			$current = 'current';

			$html .= '<ul class="profiled-tabs">';

			foreach ($tabs as $tabName => $info) {
				if (is_array($info)) {
					if ($fullLinks) {
						$html .= '<li class="tab ' . $current . '"><a href="' . $baseLink . '#' . self::TabIDPrefix . $tabName . self::TabIDSuffix . '">' . ModularUtils::decamel($tabName) . '</a></li>';
					} else {
						$html .= '<li class="tab ' . $current . '"><a href="#' . self::TabIDPrefix . $tabName . self::TabIDSuffix . '">' . ModularUtils::decamel($tabName) . '</a></li>';
					}
				} else {
					// if $info is not an array then it is a link
					$html .= '<li class="' . $current . '"><a href="' . $info . '">' . ModularUtils::decamel($tabName) . '</a></li>';
				}
				$current = '';
			}

			$html .= '</ul>';
		}

		return $html;
	}

	/**
	 * Filter out elements in $data that don't exist in config.form_fields.action
	 *
	 * @param $action
	 * @param $data
	 */
	protected static function filter_data($action, $data) {
		$formFields = static::form_fields($action);
		foreach ($data as $fieldName => $_) {
			if (!isset($formFields[$fieldName])) {
				unset($data[$fieldName]);
			}
		}
		return $data;
	}

	protected static function add_fields($action, FieldList $fields, $validator = null) {
		foreach (static::form_fields($action) as $fieldName => $info) {
			if (is_callable($info)) {

				$fields->push($info($fieldName));

			} else {
				$fields->push(static::make_field($fieldName, $info));

				list($required) = static::get_field_info($fieldName, $info);

				if ($required && $validator instanceof RequiredFields) {
					$validator->addRequiredField($fieldName);
				}
			}
		}
	}

	protected static function add_actions($action, FieldList $actions) {
		foreach (static::form_actions($action) as $info) {
			if (is_callable($info)) {
				$actions->push($info($action));
			} else {
				$actions->push(static::make_action($action, $info));
			}
		}

	}

	protected static function tabify($action, FieldList $fields) {
		$tabs = ModularModule::get_config_setting(
			get_called_class(),
			'tabs',
			$action
		) ?: [];

		if ($tabs) {
			// check if we need to show the tab strip in the form
			if (static::config()->get('show_tab_strip_in_form')) {
				$fields->push(new LiteralField('TabsTrip', self::tab_strip($action)));
			}

			$current = 'current';

			// if tabs then scan through and move fields to 'their' tab
			foreach ($tabs as $tabName => $tabFieldNames) {
				$tabFields = [];

				if (is_array($tabFieldNames)) {
					// if it's not an array it's a link so ignore it here

					foreach ($tabFieldNames as $tabFieldName) {
						// look for wildcard
						if (false === strpos($tabFieldName, '*')) {
							// no wildcard, do simple field find and move

							if ($field = $fields->dataFieldByName($tabFieldName)) {
								$fields->removeByName($tabFieldName);

								$tabFields[$tabFieldName] = $field;
							}
						} else {
							// wildcard so have to scan all fields by name and match
							foreach ($fields as $field) {
								$fieldName = $field->getName();

								if (fnmatch($tabFieldName, $fieldName)) {
									$fields->removeByName($fieldName);

									$tabFields[$fieldName] = $field;
								}
							}
						}
					}
					if ($tabFields) {
						$tab = new ProfiledTabField($tabFields);

						$tab->addExtraClass("profiled-tab-body $current");
						$tab->setTabID(self::TabIDPrefix . $tabName . self::TabIDSuffix);

						$fields->push($tab);

						$current = '';
					}
				}
			}
			Requirements::javascript('profiled/js/profiled-tabs.js');
		}
	}

	public static function form_fields($for) {
		return ModularModule::get_config_setting(get_called_class(), 'form_fields', $for);
	}

	public static function form_actions($for) {
		return ModularModule::get_config_setting(get_called_class(), 'form_actions', $for);
	}

	public static function make_field($fieldName, array $info, $value = null) {
		list($_, $className, $label) = self::get_field_info($fieldName, $info);

		/** @var FormField $field */
		$field = $className::create(
			$fieldName,
			$label,
			$value
		);
		$field->setAttribute('placeholder', $label);
		return $field;
	}

	/**
	 * @param            $fieldName
	 * @param array|bool $info array of information about the field, or a boolean requiredness
	 * @return array [required, fieldType, fieldLabel]
	 */
	protected static function get_field_info($fieldName, $info) {
		return array_replace(
			[
				true,
				'TextField',
				static::get_field_label($fieldName),
			],
			is_array($info) ? $info : [$info]
		);
	}

	public static function make_action($action, $info) {
		list($action, $label, $className) = self::get_action_info($action, $info);
		return $className::create(
			$action,
			$label
		);
	}

	/**
	 * @param       $action
	 * @param array $info
	 * @return array [ action, fieldLabel, fieldType ]
	 */
	protected static function get_action_info($action, array $info) {
		return array_merge(
			[
				$action,
				static::get_field_label(isset($info[1]) ? $info[1] : $action),
				'FormAction',
			],
			$info
		);
	}

	public static function get_field_label($fieldName, $default = null, array $data = []) {
		return _t(get_called_class() . ".$fieldName." . self::LabelSuffix, $default ?: ModularUtils::decamel($fieldName), $data);
	}

	/**
	 * @param string      $messageOrLangKey key for localised yml e.g. MemberPreferencesForm.PreferencesSaved or
	 *                                      default message
	 * @param string|null $default          optional textual default if not using decamelized $messageOrLangKey
	 * @param array       $data
	 * @return string
	 */
	public static function get_form_message($messageOrLangKey, $default = null, array $data = []) {
		return _t(get_called_class() . ".$messageOrLangKey", $default ?: ModularUtils::decamel($messageOrLangKey), $data);
	}

	public static function set_form_message($code, $type, array $data = []) {
		Session::setFormMessage(static::get_full_name(), static::get_form_message($code, null, $data), $type);
	}

	public static function clear_form_message() {
		Session::setFormMessage(static::get_full_name(), '', '');
	}

	/**
	 * @return string the full name
	 */
	public static function get_full_name() {
		return get_called_class() . '_' . get_called_class();
	}
}