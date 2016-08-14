<?php
namespace Modular\Fields;

use URLSegmentFilter;
use DataList;
use Permission;

/**
 * A field which if blank takes the value of another model field mangled to be same
 * format as a URLSegment. Does not change if the source field changes, only if it is empty.
 */
class ModelTag extends Field {
	const FieldName   = 'ModelTag';
	const FieldSchema = 'Varchar(64)';

	protected $sourceFieldName = '';
	protected $parentIDFieldName = '';

	private static $can_edit_permissions = ['ADMIN'];

	/**
	 * ModelTag constructor can be used in config to set alternate field names as:
	 * extensions:
	 *  - Modular\Fields\ModelTag('Title', 'ParentID')
	 *
	 * @param string $sourceFieldName   e.g. 'Title'
	 * @param string $parentIDFieldName e.g. 'ParentID', if passed then make the URLSegment distinct within children of the Parent by ID.
	 */
	public function __construct($sourceFieldName = 'Title', $parentIDFieldName = '') {
		$this->sourceFieldName = $sourceFieldName;
		$this->parentIDFieldName = $parentIDFieldName;
		parent::__construct();
	}

	/**
	 * Add db fields here so can use late static binding for self.FieldName and self.FieldSchema.
	 *
	 * @param string $class
	 * @param string $extension
	 * @return array
	 */
	public function extraStatics($class = null, $extension = null) {
		$parent = parent::extraStatics($class, $extension) ?: [];
		return array_merge_recursive(
			$parent,
			[
				'db' => [
					static::FieldName => static::FieldSchema,
				],
			]
		);
	}

	/**
	 * Returns the name of the field on the model used to generate the tag, e.g. the 'Title' field.
	 *
	 * @return string
	 */
	public function getSourceFieldName() {
		return $this->sourceFieldName;
	}

	/**
	 * Add as editable field if current user has permissions from config.can_edit_permissions.
	 *
	 * @return array
	 */
	public function cmsFields() {
		if (Permission::check(static::config()->get('can_edit_permissions'))) {
			return [
				new \TextField(
					static::FieldName
				),
			];
		} else {
			return [
				new \ReadonlyField(
					static::FieldName
				),
			];
		}
	}

	/**
	 * If the ModelTag field is empty (length 0) then fill it with generated value.
	 */
	public function onBeforeWrite() {
		parent::onBeforeWrite();
		if (0 === strlen($this()->{static::FieldName})) {
			$this()->{static::FieldName} = $this->generateValue();
		}
	}

	/**
	 * Generates and returns a unique value from the extended models self.otherTitleField.
	 *
	 * @param int $increment if provided this is appended to generated value and checked again to see if it exists
	 *
	 * @return string
	 */
	public function generateValue($increment = null) {
		$filter = new URLSegmentFilter();

		$sourceValue = $this()->{$this->sourceFieldName};

		$urlSegment = $filter->filter($sourceValue);

		if (is_int($increment)) {
			$urlSegment .= '-' . $increment;
		}
		/** @var DataList $duplicate */
		$duplicate = DataList::create($this()->ClassName)->filter([
			static::FieldName => $urlSegment,
		]);

		if ($this->parentIDFieldName && $this()->hasField($this->parentIDFieldName) && $this()->{$this->parentIDFieldName}) {
			$duplicate = $duplicate->exclude([
				$this->parentIDFieldName => $this()->{$this->parentIDFieldName},
			]);
		}

		if ($this()->ID) {
			$duplicate = $duplicate->exclude([
				'ID' => $this()->ID
			]);
		}

		if ($duplicate->count() > 0) {
			if (is_int($increment)) {
				$increment += 1;
			} else {
				$increment = 0;
			}

			$urlSegment = $this->generateValue((int) $increment);
		}

		return $urlSegment;
	}
}