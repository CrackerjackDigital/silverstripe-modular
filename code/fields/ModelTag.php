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
	protected $fallbackFieldName = '';

	private static $can_edit_permissions = ['ADMIN'];

	private static $hierarchical_tag_seperator = ':';

	private static $hierarchical_source_seperator = ' > ';

	/**
	 * ModelTag constructor can be used in config to set alternate field names as:
	 * extensions:
	 *  - Modular\Fields\ModelTag('Title', 'ParentID')
	 *
	 * @param string $sourceFieldName   e.g. 'Title'
	 * @param string $parentIDFieldName e.g. 'ParentID', if passed then make the tag distinct within children of the ParentID.
	 * @param string $fallbackFieldName e.g. 'URLSegment', allow another field to be used on parents if they do not have the ModelTag extension
	 */
	public function __construct($sourceFieldName = 'Title', $parentIDFieldName = '', $fallbackFieldName = '') {
		$this->sourceFieldName = $sourceFieldName;
		$this->parentIDFieldName = $parentIDFieldName;
		$this->fallbackFieldName = $fallbackFieldName;
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
	 * Return the tag value from the extended model.
	 *
	 * @return string|null
	 */
	public function ModelTag() {
		return $this()->{static::FieldName};
	}

	/**
	 * Generate a full tag path walking up the chain via parents seperated by config.hierarchical_source_seperator.
	 * If the parent doesn't have ModelTag extension then self.fallbackFieldName will be used if supplied in extension ctor.
	 *
	 * TODO test/debug this
	 *
	 * @return string
	 */
	public function HierarchicalModelTag() {
		$tag = '';

		if ($parentID = $this->ParentID()) {
			if ($parent = $this()->{$this->parentIDFieldName}()) {
				if ($parent->hasExtension('ModelTag')) {
					$tag = $parent->HierarchicalModelTag() . $this->config()->get('hierarchical_tag_seperator');
				} elseif ($parent->hasField(static::FieldName)) {
					$tag = $parent->{static::FieldName} . $this->config()->get('hierarchical_tag_seperator');
				} elseif ($this->fallbackFieldName && $parent->hasField($this->fallbackFieldName)) {
					$tag = $parent->{$this->fallbackFieldName} . $this->config()->get('hierarchical_tag_seperator');
				}
			}
		}
		return $tag . $this->ModelTag();
	}

	/**
	 * Generates a full tag path using the models 'Source' field (e.g. 'Title') instead of the tag field via the ParentID relationship specified
	 * in the extension ctor. Components will be seperated by config.hierarchical_source_seperator
	 *
	 *
	 * @return string
	 */
	public function HierarchicalSource() {
		$title = '';

		if ($parentID = $this->ParentID()) {
			if ($parent = $this()->{$this->parentIDFieldName}()) {
				if ($parent->hasExtension('ModelTag')) {
					$title = $parent->HierarchicalSource() . $this->config()->get('hierarchical_source_seperator');
				} elseif ($parent->hasField($this->sourceFieldName)) {
					// can only go one level up here
					$title = $parent->{$this->sourceFieldName} . $this->config()->get('hierarchical_source_seperator');
				}
			}
		}
		return $title . $this()->{$this->sourceFieldName};
	}

	/**
	 * If a parent ID field name was specfied in extension ctor then return the ParentID value from
	 * the extended model.
	 *
	 * @return mixed
	 */

	public function ParentID() {
		if ($this->parentIDFieldName && $this()->hasField($this->parentIDFieldName)) {
			return $this()->{$this->parentIDFieldName};
		}
		return null;
	}

	/**
	 * Generates and returns a unique value from the extended models self.otherTitleField. Will check if same
	 * tag exists under same parent if a ParentID was supplied when extension initialised (see ctor above). If no
	 * parent ID then tags will be unique across the site for the model.
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

		if ($parentID = $this->ParentID()) {
			$duplicate = $duplicate->exclude([
				$this->parentIDFieldName => $parentID,
			]);
		}

		if ($this()->ID) {
			$duplicate = $duplicate->exclude([
				'ID' => $this()->ID,
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