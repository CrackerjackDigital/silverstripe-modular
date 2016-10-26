<?php
namespace Modular\Fields;

use Modular\reflection;
use URLSegmentFilter;
use DataList;
use Permission;

/**
 * A field which if blank takes the value of another model field mangled to be same
 * format as a URLSegment. Does not change if the source field changes, only if it is empty.
 */
class ModelTag extends Field {
	use reflection;

	const SingleFieldName   = 'ModelTag';
	const SingleFieldSchema = 'Varchar(64)';

	protected $sourceFieldName = '';
	protected $parentIDFieldName = '';
	protected $fallbackFieldName = '';

	private static $can_edit_permissions = ['ADMIN'];

	private static $hierarchical_tag_separator = ':';

	private static $hierarchical_source_separator = ' > ';

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

	public function fieldDecorationTokens() {
		return array_merge(
			parent::fieldDecorationTokens(),
			[
				'modelTag' => $this->singleFieldValue()
			]
		);
	}

	/**
	 * Return all the page models related to this tag (there may be multiple Page Classe attached so iterate through them all)
	 *
	 * @param string|array $matchClassNames and array of classnames, a class name or a pattern as used by fnmatch, eg. '*Page'
	 * @return \ArrayList
	 */
	public function relatedByClassName($matchClassNames) {
		$matchClassNames = is_array($matchClassNames) ? $matchClassNames : [$matchClassNames ];

		$pages = new \ArrayList();
		foreach ($this()->config()->get('belongs_many_many') as $relationship => $className) {
			foreach ($matchClassNames as $pattern) {
				if (fnmatch($pattern, $className)) {
					$pages->merge($this()->$relationship());
				}
			}
		}
		return $pages;
	}

	/**
	 * Encode a value as it would be for a ModelTag (basically a URLSegment)
	 * @param $value
	 * @return String
	 */
	public static function encode($value) {
		static $urlSegmentFilter;
		if (!$urlSegmentFilter) {
			$urlSegmentFilter = new \URLSegmentFilter();
		}
		return $urlSegmentFilter->filter($value);
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
	 * If the ModelTag field is empty (length 0) then fill it with generated value.
	 */
	public function onBeforeWrite() {
		parent::onBeforeWrite();
		if (0 === strlen($this()->{static::SingleFieldName})) {
			$this()->{static::SingleFieldName} = $this->generateValue();
		}
	}

	/**
	 * Generate a full tag path walking up the chain via parents separated by config.hierarchical_source_separator.
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
					$tag = $parent->HierarchicalModelTag() . $this->config()->get('hierarchical_tag_separator');
				} elseif ($parent->hasField(static::SingleFieldName)) {
					$tag = $parent->{static::SingleFieldName} . $this->config()->get('hierarchical_tag_separator');
				} elseif ($this->fallbackFieldName && $parent->hasField($this->fallbackFieldName)) {
					$tag = $parent->{$this->fallbackFieldName} . $this->config()->get('hierarchical_tag_separator');
				}
			}
		}
		return $tag . $this()->ModelTag;
	}

	/**
	 * Generates a full tag path using the models 'Source' field (e.g. 'Title') instead of the tag field via the ParentID relationship specified
	 * in the extension ctor. Components will be separated by config.hierarchical_source_separator
	 *
	 *
	 * @return string
	 */
	public function HierarchicalSource() {
		$title = '';

		if ($parentID = $this->ParentID()) {
			if ($parent = $this()->{$this->parentIDFieldName}()) {
				if ($parent->hasExtension('ModelTag')) {
					$title = $parent->HierarchicalSource() . $this->config()->get('hierarchical_source_separator');
				} elseif ($parent->hasField($this->sourceFieldName)) {
					// can only go one level up here
					$title = $parent->{$this->sourceFieldName} . $this->config()->get('hierarchical_source_separator');
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
		$sourceValue = $this()->{$this->sourceFieldName};

		$urlSegment = static::encode($sourceValue);

		if (is_int($increment)) {
			$urlSegment .= '-' . $increment;
		}
		/** @var DataList $duplicate */
		$duplicate = DataList::create($this()->ClassName)->filter([
			static::SingleFieldName => $urlSegment,
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