<?php
namespace Modular\Fields;

use DateField;
use DatetimeField;
use FieldList;
use FormField;
use LiteralField;
use Modular\Exceptions\Exception;
use Modular\lang;
use Modular\Model;
use Modular\ModelExtension;
use SS_List;
use TimeField;
use ValidationException;
use ValidationResult;

/**
 * Validation rules from the extensions config.validation are formatted as a map of:
 *
 *  'FieldName' => [ minlength, maxlength, pattern ]
 *
 * - or -
 *
 *  'FieldName' => true | false
 *
 * where pattern is a preg expression and minlength/maxlength are integers (may be 0 for don't care)
 * - a minlength of > 0 or a boolean true means required
 * - a maxlength of 0 means no limit
 *
 * @property \Modular\Model $owner
 */
abstract class Field extends ModelExtension {
	use lang;

	// if SingleFieldName and SingleFieldSchema are provided then they will be added to the
	// config.db array by extraStatics
	const SingleFieldName   = '';
	const SingleFieldSchema = '';

	const RelationshipName = '';

	const DefaultUploadFolderName = 'incoming';

	const ValidationRulesConfigVarName = 'validation';

	const DefaultTabName = 'Root.Main';

	// Zend_Locale_Format compatible format string, if blank then default for locale is used
	private static $time_field_format = '';

	private static $cms_tab_name = '';

	/**
	 * If we use invocation we can type-cast the result to a ModularModel
	 *
	 * @return Model
	 */
	public function __invoke() {
		return $this->owner;
	}
	
	/**
	 * @param mixed|null $set if provided sets the value on the model for SingleFieldValue and return this, otherwise
	 *                        returns the models SingleFieldValue.
	 * @return $this
	 * @throws Exception if SingleFieldName constant is not set via late static binding
	 * @getter-setter
	 * @fluent-setter
	 */
	public function singleFieldValue($set = null) {
		if (!static::SingleFieldName) {
			throw new Exception("Called singleFieldValue() with no SingleFieldName set");
		}
		if (func_num_args()) {
			$this()->{static::SingleFieldName} = $set;
			return $this;
		} else {
			return $this()->{static::SingleFieldName};
		}
	}

	/**
	 * Override in concrete classes to provide an array of fields which this extension adds.
	 *
	 * If SingleFieldName and SingleFieldSchema constants are set then will scaffold a suitable
	 * for field for them, however complex fields requiring drop-down lists etc won't be populated.
	 *
	 * @return array
	 */
	public function cmsFields() {
		$fields = [];

		if (static::SingleFieldName && static::SingleFieldSchema) {
			if ($dbField = $this()->dbObject(static::SingleFieldName)) {
				if ($formField = $dbField->scaffoldFormField()) {
					$fields[ static::SingleFieldName ] = $formField;
				}
			}
		}
		return $fields;
	}

	/**
	 * Return the name (path) of the tab in the cms this model's fields should show under from
	 * config.cms_tab_name in:
	 *
	 * this extension or if not set from
	 * the extended model or if not set
	 * then self.DefaultTabName.
	 *
	 * @return string
	 */
	protected function cmsTab() {
		return $this->config()->get('cms_tab_name')
			?: static::DefaultTabName;
	}

	/**
	 * If static.SingleFieldName && static.SingleFieldSchema are set add them to db array.
	 *
	 * @param null $class
	 * @param null $extension
	 * @return mixed
	 */
	public function extraStatics($class = null, $extension = null) {
		return array_merge_recursive(
			parent::extraStatics($class, $extension) ?: [],
			(static::SingleFieldName && static::SingleFieldSchema)
				? ['db' => [static::SingleFieldName => static::SingleFieldSchema]]
				: []
		);
	}

	/**
	 * Update summary fields to use Label from localisation yml if it exists.
	 *
	 * @param array $fields
	 */
	public function updateSummaryFields(&$fields) {
		if (static::SingleFieldName) {
			$fields[ static::SingleFieldName ] = $this->fieldDecoration(
				static::SingleFieldName,
				'Label',
				isset($fields[ static::SingleFieldName ])
					? $fields[ static::SingleFieldName ]
					: static::SingleFieldName
			);
		}
	}

	/**
	 * Update form fields to have:
	 *  label, guide and description from lang.yml
	 *  minlength, maxlength and pattern from config.validation
	 *
	 */
	public function updateCMSFields(FieldList $fields) {
		$allFieldsConstraints = $this->config()->get(static::ValidationRulesConfigVarName) ?: [];

		if ($cmsFields = $this->cmsFields()) {
			/** @var FormField $field */
			foreach ($cmsFields as $field) {
				$fieldName = $field->getName();

				if ($fieldName) {
					// remove any existing field with this name already added e.g. by cms scaffolding.
					$fields->removeByName($fieldName);

					$this->addHTMLAttributes($field);

					$this->setFieldDecorations($field);

				}
				// add any extra constraints, display-logic etc on a per-field basis
				$this()->extend('customFieldConstraints', $field, $allFieldsConstraints);
			}
			$fields->addFieldsToTab(
				$this->cmsTab(),
				$cmsFields
			);
		}

	}

	/**
	 * Return a map of fieldname => value for data relevant to only this extension.
	 *
	 * @return array
	 */
	public function extendedFieldData() {
		$fieldNames = $this->extendedFieldNames();
		return array_intersect_key(
			$this()->toMap(),
			array_flip($fieldNames)
		);
	}

	/**
	 * Returns a numerically keyed map of field names relevant to this extension.
	 *
	 * @return array
	 */
	public function extendedFieldNames() {
		$fields = $this->cmsFields();

		$fieldNames = array_map(
			function ($field) {
				return $field->getName();
			},
			$fields
		);
		return $fieldNames;
	}

	/**
	 * Add any additional constraints, display_logic logic etc, this is called by extension on the extended model.
	 *
	 * TODO rename to extendedFieldConstraints
	 *
	 * @param \FormField $field
	 * @param array      $allFieldConstraints
	 */
	public function customFieldConstraints(FormField $field, array $allFieldConstraints) {
		// default does nothing, this is mainly here for the method template when deriving classes
	}

	/**
	 * Returns the RelationshipName for this field if set, optionally appended with the fieldName as for a relationship.
	 *
	 * @param string $fieldName if supplied will be added on to RelationshipName with a '.' prefix
	 * @return string
	 */
	public static function relationship_name($fieldName = '') {
		return static::RelationshipName ? (static::RelationshipName . ($fieldName ? ".$fieldName" : '')) : '';
	}

	public static function field_name($suffix = '') {
		return static::SingleFieldName  ? (static::SingleFieldName . $suffix) : '';
	}

	/**
	 * Set field decorations, e.g. label, guide information etc
	 *
	 * @param \FormField $field
	 */
	protected function setFieldDecorations(FormField $field) {
		$fieldName = $field->getName();

		$field->setTitle(
			$this->fieldDecoration($fieldName, "Label", $field->Title(), [], $field)
		);
		$guide = $this->fieldDecoration($fieldName, "Guide", '', [], $field);

		$field->setRightTitle(
			$guide
		);
		if ($field instanceof \CheckboxField) {
			$field->setDescription($guide);
		}
	}

	/**
	 * Add any attributes, e.g. html5 validation, placeholder
	 *
	 * @param \FormField $field
	 */
	protected function addHTMLAttributes(FormField $field) {
		$fieldName = $field->getName();

		$field->setAttribute('placeholder', $this->fieldDecoration($fieldName, "Placeholder", '', [], $field));

		if (isset($allFieldsConstraints[ $fieldName ])) {
			// add html5 validation attributes
			list($minlength, $maxlength, $pattern) = $allFieldsConstraints[ $fieldName ];

			if (!is_null($minlength)) {
				$field->setAttribute('minlength', $minlength);
			}
			if (!is_null($maxlength)) {
				$field->setAttribute('maxlength', $maxlength);
			}
			if (!is_null($pattern)) {
				$field->setAttribute('pattern', $pattern);
			}
		}
	}

	/**
	 * Validates fields according to their validation rules, specifically
	 *
	 * @param \ValidationResult $result
	 * @return array of messages added to result object
	 * @throws \ValidationException
	 */
	public function validate(ValidationResult $result) {
		$this()->extend('onBeforeValidate', $result);

		$messages = [];
		$cmsFields = $this->cmsFields();

		if ($cmsFields) {

			// if one is defined all need to be defined
			/** @var FormField $field */
			foreach ($cmsFields as $field) {
				$fieldName = $field->getName();
				$fieldConstraints = $this->fieldConstraints($fieldName, [0, 0, '']);

				//if there are no validation rules for this field, or they are 'empty' rules move onto the next one
				if (!$fieldConstraints || $fieldConstraints == [0, 0, '']) {
					continue;
				}

				// deconstruct the constraints
				list($minlength, $maxlength, $pattern) = $fieldConstraints;

				$lengthType = null;
				$length = 0;

				if ($this()->hasField($fieldName . 'ID')) {
					// get the title before we append ID
					$lengthType = $field->Title() ?: $fieldName;

					$fieldName = $fieldName . 'ID';
					$length = $this()->$fieldName ? 1 : 0;

				} elseif ($this()->hasMethod($fieldName)) {
					if ($value = $this()->$fieldName()) {
						if ($value instanceof SS_List) {
							$length = $value->count();
							$lengthType = $this()->i18n_plural_name();
						}
					}
				} elseif ((substr($fieldName, -2, 2) == 'ID') && $this()->hasMethod(substr($fieldName, -2 - 2))) {
					$length = $this()->$fieldName();
					$lengthType = $this()->i18n_singular_name();
				}
				if (is_null($lengthType)) {
					$value = $this()->$fieldName;

					if (is_array($value)) {
						$length = count($value);
						$lengthType = 'choice';
					} else {
						// need to strip tags to get a realistic length on html fields, just leave white-space out of count
						$length = $this->valueLength($value);
						$lengthType = 'letter';
					}
				}

				if ($pattern) {
					// set start and end pattern of '~' so we can use slashes in the config file
					// and make regexps just a we bit more friendly.
					$pattern = '~' . trim($pattern, '/~') . '~';

					if (false === preg_match($pattern, $value)) {
						// add pattern error message to $messages
						$messages[] = $this->fieldDecoration(
							$fieldName,
							"Format", "be in format {pattern}",
							[
								'pattern' => $pattern,
							],
							$field
						);
					}
				}

				//validate that value falls between the min and max length
				$lengthMessage = '';
				if ($minlength != $maxlength) {
					if ($minlength && ($length < $minlength)) {
						if ($minlength == 1) {
							$lengthMessage = 'be provided';
						} else {
							$lengthMessage = "have at least {minlength} $lengthType" . ($minlength > 1 ? 's' : '');
						}
					}
					if ($maxlength && ($length > $maxlength)) {
						$lengthMessage = "have at most {maxlength} $lengthType" . ($maxlength > 1 ? 's' : '');
					}
				} else {
					if ($minlength && ($length < $minlength)) {
						if ($minlength == 1) {
							$lengthMessage = 'be provided';
						} else {
							$lengthMessage = "{minlength} $lengthType" . ($minlength > 1 ? 's' : '');;
						}
					}
				}
				if ($lengthMessage) {
					$messages[] = $this->fieldDecoration(
						$fieldName, "Length", $lengthMessage,
						[
							'minlength' => $minlength,
							'maxlength' => $maxlength,
							'pattern'   => $pattern,
						],
						$field
					);
				}

				//if there were any error messages, set the error result and throw exception
				if ($messages) {
					$message = $this->fieldDecoration(
						$fieldName,
						"Label",
						"{label} should " . implode(' and ', $messages),
						[
							'label' => $field->Title() ?: $fieldName,
						]
					);

					$result->error($message);

					throw new ValidationException($result, $message);
				}
			}
		}
	}

	/**
	 * Return a stripped out length of a value excluding whitespace and tags.
	 *
	 * @param $value
	 * @return int
	 */
	protected function valueLength($value) {
		return strlen(strip_tags(preg_replace('/\s+/', '', $value)));
	}

	/**
	 * If a fieldName is a relationship name then returns a nice label for the remote class name, otherwise empty array.
	 * Only handles many_many at the moment.
	 *
	 * TODO: handle has_many and has_one
	 *
	 * @param $fieldName
	 * @return array of [singular, plural] names or empty array if not found.
	 */
	protected function labelsForRelatedClass($fieldName) {
		if ($manyMany = $this()->manyManyComponent($fieldName)) {
			while ($relatedClassName = array_shift($manyMany)) {

				if ($relatedClassName == $this()->class) {
					$singleton = singleton($relatedClassName);
					return [
						$singleton->i18n_singular_name(),
						$singleton->i18n_plural_name(),
					];
				}
				// shift again as manyManyComponent returns interleaved array of relatedClassName/class
				array_shift($manyMany);
			}
		} elseif ($hasMany = $this()->hasManyComponent($fieldName)) {
			// TODO: handle has_many
			// xdebug_break();
		} elseif ($hasOne = $this()->hasOneComponent($fieldName)) {
			// TODO: handle has_one
			// xdebug_break();
		}
		return [];
	}

	/**
	 * Configure date fields to be in various states as per parameter options.
	 *
	 * @param \DateField $field
	 * @param bool       $showMultipleFields
	 * @return \DateField
	 */
	protected function configureDateField(DateField $field, $showMultipleFields = true) {
		if ($showMultipleFields) {
			$field->setConfig('dmyfields', true)
				->setConfig('dmyseparator', ' / ')// set the separator
				->setConfig('dmyplaceholders', 'true'); // enable HTML 5 Placeholders
		}
		return $field;
	}
	
	/**
	 * @param \TimeField $field
	 * @param bool       $showMultipleFields
	 * @return TimeField
	 */
	protected function configureTimeField(TimeField $field, $showMultipleFields = true) {
		if ($showMultipleFields) {
			// if not set then the default will be used for the locale
			if ($format = $this->config()->get('time_field_format')) {
				$field->setConfig('timeformat', $format);
			}
		}
		return $field;
	}
	
	/**
	 * Configures the Date and Time fields in the wrapping DatetimeField.
	 *
	 * @param \DatetimeField $field
	 * @param bool           $showMultipleFields
	 * @return DatetimeField
	 */
	protected function configureDateTimeField(DatetimeField $field, $showMultipleFields = true) {
		if ($showMultipleFields) {
			$this->configureDateField($field->getDateField(), $showMultipleFields);
			$this->configureTimeField($field->getTimeField(), $showMultipleFields);
		}
		return $field;
	}

	/**
	 * Returns an array of field constraints for the named field as defined in config.validation, so
	 * [minlength, maxlength, pattern] or defaults (which is no checks) if not found.
	 *
	 * @param string $fieldName
	 * @param array  $defaults to use if not found in config for that field = no validation performed
	 * @return array
	 */
	public function fieldConstraints($fieldName, array $defaults = [0, 0, '']) {
		$allFieldsConstraints = array_merge(
			$this->config()->get(static::ValidationRulesConfigVarName) ?: [],
			$this()->config()->get(static::ValidationRulesConfigVarName) ?: []
		);
		$constraints = $defaults;;

		foreach ([$fieldName, $fieldName . 'ID'] as $name) {
			if (isset($allFieldsConstraints[ $name ])) {
				if (is_bool($allFieldsConstraints[ $name ])) {
					// use the boolean as the min length, could be 0 or 1 which is enough
					$constraints = [(int) $allFieldsConstraints[ $name ], 0, ''] + $defaults;
				} else {
					// presume it's an array or something else we handle
					$constraints = $allFieldsConstraints[ $name ];
				}
				break;
			}
		}
		return $constraints;
	}

	protected function saveMasterHint() {
		return new LiteralField(
			static::RelationshipName . 'Hint',
			$this->fieldDecoration(
				static::RelationshipName,
				'SaveMasterHint',
				"<b>Please save the master first</b>"
			)
		);
	}
}
