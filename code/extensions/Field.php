<?php
namespace Modular\Fields;

use DateField;
use DatetimeField;
use DisplayLogicWrapper;
use FieldList;
use File;
use FormField;
use GridField;
use GridFieldOrderableRows;
use LiteralField;
use Modular\Exception;
use Modular\GridField\GridFieldConfig;
use Modular\Model;
use Modular\ModelExtension;
use SS_List;
use TimeField;
use UploadField;
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
	const UploadFolderName = 'incoming';

	const ValidationRulesConfigVarName = 'validation';

	// override in derived classes to use a specialised config not manufactured from extension name
	const GridFieldConfigName = '';

	const GridFieldOrderableRowsFieldName = 'Sort';

	// override in concrete e.g. 'Blocks' or 'AssociatedRecords'
	const RelationshipName = '';

	// TODO remove not used?
	private static $num_grid_rows = 5;

	// TODO remove not used?
	private static $gridfield_config_class = '';

	// Zend_Locale_Format compatible format string, if blank then default for locale is used
	private static $time_field_format = '';

	/**
	 * If we use invocation we can type-cast the result to a ModularModel
	 *
	 * @return Model
	 */
	public function __invoke() {
		return $this->owner;
	}

	/**
	 * Should override in concrete classes to provide an array of fields which this extension adds.
	 *
	 * @return array
	 */
	public function cmsFields() {
		return [];
	}

	/**
	 * Update form fields to have:
	 *  label, guide and description from lang.yml
	 *  minlength, maxlength and pattern from config.validation
	 *
	 */
	public function updateCMSFields(FieldList $fields) {
		$allFieldsConstraints = $this->config()->get(static::ValidationRulesConfigVarName) ?: [];

		$cmsFields = $this->cmsFields();

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
	 * Set field decorations, e.g. label, guide information etc
	 *
	 * @param \FormField $field
	 */
	protected function setFieldDecorations(FormField $field) {
		$fieldName = $field->getName();

		$label = $this->translatedMessage($fieldName, "Label", $field->Title(), [], $field);
		$guide = $this->translatedMessage($fieldName, "Guide", '', [], $field);

		$field->setTitle($label);
		$field->setRightTitle($guide);
	}

	/**
	 * Add any attributes, e.g. html5 validation, placeholder
	 *
	 * @param \FormField $field
	 */
	protected function addHTMLAttributes(FormField $field) {
		$fieldName = $field->getName();

		$field->setAttribute('placeholder', $this->translatedMessage($fieldName, "Placeholder", '', [], $field));

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
	 * Return a GridField configured for editing attached MediaModels. If the master record is in the database
	 * then also add GridFieldOrderableRows (otherwise complaint re UnsavedRelationList not being a DataList happens).
	 *
	 * @param string|null $relationshipName
	 * @param string|null $configClassName name of grid field configuration class otherwise one is manufactured
	 * @return \GridField
	 */
	protected function gridField($relationshipName = null, $configClassName = null) {
		$relationshipName = $relationshipName
			?: static::RelationshipName;

		$configClassName = $configClassName
			?: static::GridFieldConfigName
				?: get_class($this) . 'GridFieldConfig';

		/** @var GridFieldConfig $config */
		$config = $configClassName::create();

		/** @var GridField $gridField */
		$gridField = GridField::create(
			$relationshipName,
			$relationshipName,
			$this->owner->$relationshipName(),
			$config
		);

		if ($this()->isInDB()) {
			// only add if this record is already saved
			$config->addComponent(
				new GridFieldOrderableRows(static::GridFieldOrderableRowsFieldName)
			);
		}

		return $gridField;
	}

	/**
	 * Validates fields according to their validation rules, specifically
	 *
	 * @param \ValidationResult $result
	 * @return array of messages added to result object
	 * @throws \ValidationException
	 */
	public function validate(ValidationResult $result) {
		$messages = [];
		$cmsFields = $this->cmsFields();

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

			/** @var SS_List|mixed|null $value */
			if ($this()->hasMethod($fieldName)) {
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
					$messages[] = $this->translatedMessage(
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
				$messages[] = $this->translatedMessage(
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
				$message = $this->translatedMessage(
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
	 * Return an upload field wrapped in a DisplayLogicWrapper as they all should be when using displaylogic.
	 *
	 * @param $fieldName
	 * @return \DisplayLogicWrapper
	 * @throws \Exception
	 */
	public function makeUploadField($fieldName) {
		$wrapper = (new DisplayLogicWrapper(
			$field = new UploadField(
				$fieldName
			)
		))->setID($fieldName)->setName($fieldName);
		return $wrapper;
	}

	/**
	 * @param UploadField|DisplayLogicWrapper $field
	 * @param string                          $configVarName - allow you to switch config to check e.g. 'allowed_video_files'
	 * @throws \Exception
	 */
	protected function configureUploadField($field, $configVarName = 'allowed_files') {
		$fieldName = $field->getName();
		if ($field instanceof DisplayLogicWrapper) {
			// drill down into wrapper to get actual UploadField
			$field = $field->fieldByName($fieldName);
		}

		list($minlength, $maxlength, $pattern) = $this->fieldConstraints($fieldName, [0, 0, '']);

		$field->setAllowedMaxFileNumber($maxlength ?: null);
		// don't allow existing media to be re-attached it's a has_one so would be messy
		$field->setCanAttachExisting(false);
		$field->setFolderName($this->uploadFolderName());

		// could be string for category or an array of extensions
		// try model first then extension
		$extensions = $allowedFiles = $this()->allowedFileTypes($configVarName) ?: $this->config()->get($configVarName);

		if (!is_array($allowedFiles)) {
			// get extensions from category so we always get a list of extensions for the CMS right title
			$allCategoryExtensions = File::config()->get('app_categories') ?: [];
			if (isset($allCategoryExtensions[ $allowedFiles ])) {
				$extensions = $allCategoryExtensions[ $allowedFiles ];
			} else {
				$extensions = [$allowedFiles];
			}
		}
		if (is_array($allowedFiles)) {
			$field->setAllowedExtensions($extensions);
		} elseif ($allowedFiles) {
			// not an array so a category e.g. 'video'
			$field->setAllowedFileCategories($allowedFiles);
		} else {
			throw new Exception("No $configVarName configuration set");
		}

		$field->setRightTitle($this->translatedMessage(
			$fieldName, 'Label', $field->Title(), [
				'extensions' => implode(', ', $extensions),
			]
		));

	}

	/**
	 * Returns the path relative to assets/ for the particular media type, e.g 'videos', 'audio' etc.
	 *
	 * Uses config.allowed_files if not overriden by UploadFolderName const.
	 */
	public function uploadFolderName() {
		return $this()->config()->get('upload_folder') ?: static::UploadFolderName;
	}

	/**
	 * Configure date fields to be in various states as per parameter options.
	 *
	 * @param \DateField $field
	 * @param bool       $showMultipleFields
	 */
	protected function configureDateField(DateField $field, $showMultipleFields = true) {
		if ($showMultipleFields) {
			$field->setConfig('dmyfields', true)
				->setConfig('dmyseparator', ' / ')// set the separator
				->setConfig('dmyplaceholders', 'true'); // enable HTML 5 Placeholders
		}
	}

	/**
	 * @param \TimeField $field
	 * @param bool       $showMultipleFields
	 */
	protected function configureTimeField(TimeField $field, $showMultipleFields = true) {
		if ($showMultipleFields) {
			// if not set then the default will be used for the locale
			if ($format = $this->config()->get('time_field_format')) {
				$field->setConfig('timeformat', $format);
			}
		}
	}

	/**
	 * Configures the Date and Time fields in the wrapping DatetimeField.
	 *
	 * @param \DatetimeField $field
	 * @param bool           $showMultipleFields
	 */
	protected function configureDateTimeField(DatetimeField $field, $showMultipleFields = true) {
		if ($showMultipleFields) {
			$this->configureDateField($field->getDateField(), $showMultipleFields);
			$this->configureTimeField($field->getTimeField(), $showMultipleFields);
		}
	}

	/**
	 * Returns an array of field constraints for the named field as defined in config.validation, so
	 * [minlength, maxlength, pattern] or defaults (which is no checks) if not found.
	 *
	 * @param string $fieldName
	 * @param array  $defaults to use if not found in config for that field = no validation performed
	 * @return array
	 */
	protected function fieldConstraints($fieldName, array $defaults = [0, 0, '']) {
		$allFieldsConstraints = array_merge(
			$this->config()->get(static::ValidationRulesConfigVarName) ?: [],
			$this()->config()->get(static::ValidationRulesConfigVarName) ?: []
		);

		if (isset($allFieldsConstraints[ $fieldName ])) {
			if (is_bool($allFieldsConstraints[ $fieldName ])) {
				// use the boolean as the min length, could be 0 or 1 which is enough
				$constraints = [(int) $allFieldsConstraints[ $fieldName ], 0, ''] + $defaults;

			} else {
				// presume it's an array or something else we handle
				$constraints = $allFieldsConstraints[ $fieldName ];
			}
		} else {
			$constraints = $defaults;
		}
		return $constraints;
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
	protected function translatedMessage($fieldName, $decoration, $default = '', array $tokens = [], $field = null) {
		// merge in some defaults, passed tokens will override
		$tokens = array_merge(
			[
				'singular' => $this()->i18n_singular_name() ?: $this()->singular_name(),
				'plural'   => $this()->i18n_plural_name() ?: $this()->plural_name(),
			],
			($field instanceof FormField)
				? ['label' => $field->Title()]
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
						$value = _t('IntentionallyGoingToFail', $default, $tokens);
					}
				}
			}
		}
		return $value;
	}

	protected function saveMasterHint() {
		return new LiteralField(
			static::RelationshipName . 'Hint',
			$this->translatedMessage(
				static::RelationshipName,
				'SaveMasterHint',
				"<b>Please save the master first</b>"
			)
		);
	}
}
