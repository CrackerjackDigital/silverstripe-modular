<?php
namespace Modular\Fields;

use \DateField as DField;
use \TimeField as TField;
use \DatetimeField as DTField;

/**
 * A EventDate field which is distinct from the SilverStripe 'Created' field.
 */
class DateTimeField extends Field {
	// override for field name in implementation class
	const SingleFieldName   = '';
	// always use SS_DateTime for dates and date-times
	const SingleFieldSchema = 'SS_DateTime';
	// show time field or just the date field?
	const ShowTimeField     = false;
	// show Year, Month, Day, Hours, Minutes as seperated fields, one per unit
	const ShowSeperateFields = true;

	const DateRequired = true;
	const TimeRequired = false;

	public function extraStatics($class = null, $extension = null) {
		return array_merge(
			parent::extraStatics($class, $extension),
			[
				'db'         => [
					static::SingleFieldName => static::SingleFieldSchema,
				],
				'validation' => [
					static::SingleFieldName => static::DateRequired,
				],
			]
		);
	}

	public function updateSummaryFields(&$fields) {
		$fields[ static::SingleFieldName ] = $this->fieldDecoration(static::SingleFieldName);
	}

	/**
	 * Returns fields for entering date and time. NB injector has overridden the TimeField to be CERATimeField to
	 * fix a problem saving DatatimeField with no date.
	 *
	 * @return array
	 */
	public function cmsFields() {
		if (static::ShowTimeField) {
			return [
				DTField::create(
					static::SingleFieldName
				),
			];
		} else {
			return [
				DField::create(
					static::SingleFieldName
				)
			];
		}
	}

	/**
	 * Configures the date field.
	 *
	 * @param \FormField $field
	 * @param array      $allFieldConstraints
	 */
	public function customFieldConstraints(\FormField $field, array $allFieldConstraints) {
		/** @var DField $field */
		if ($field->getName() == static::SingleFieldName) {
			if ($field instanceof DTField) {
				$this->configureDateTimeField($field, static::ShowSeperateFields);
			} else {
				$this->configureDateField($field, static::ShowSeperateFields);
			}
		}
	}

	/**
	 * Configure date fields to be in various states as per parameter options.
	 *
	 * @param DField $field
	 * @param bool       $showMultipleFields
	 */
	protected function configureDateField(DField $field, $showMultipleFields = true) {
		if ($showMultipleFields) {
			$field->setConfig('dmyfields', true)
				->setConfig('dmyseparator', ' / ')// set the separator
				->setConfig('dmyplaceholders', 'true'); // enable HTML 5 Placeholders
		}
	}

	/**
	 * @param TField $field
	 * @param bool       $showMultipleFields
	 */
	protected function configureTimeField(TField $field, $showMultipleFields = true) {
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
	 * @param DTField $field
	 * @param bool           $showMultipleFields
	 */
	protected function configureDateTimeField(DTField $field, $showMultipleFields = true) {
		if ($showMultipleFields) {
			$this->configureDateField($field->getDateField(), $showMultipleFields);
			$this->configureTimeField($field->getTimeField(), $showMultipleFields);
		}
	}
}