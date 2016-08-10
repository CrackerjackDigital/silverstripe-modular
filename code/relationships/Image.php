<?php
namespace Modular\Relationships;

use Modular\Fields\Field;
use Modular\Interfaces\Imagery;
use ArrayList;
use FormField;

class Image extends Field implements Imagery {
	const RelationshipName = 'Image';
	const UploadFieldName = 'ImageID';      // keep in sync with RelationshipName
	const UploadFolderName = 'images';


	private static $has_one = [
		self::RelationshipName => 'Image'
	];

	private static $allowed_files = 'image';

	/**
	 * Return a list with only item being the single related image.
	 * @return \ArrayList
	 */
	public function Images() {
		return new ArrayList(array_filter([$this()->{self::RelationshipName}()]));
	}

	/**
	 * Return the single related image, shouldn't really get here as the extended model's field accessor should be called first.
	 * @return Image|null
	 */
	public function Image() {
		return $this()->{self::RelationshipName}();
	}

	/**
	 * Adds a single Image single-selection UploadField
	 * @return array
	 */
	public function cmsFields() {
		return [
			$this->makeUploadField(static::RelationshipName)
		];
	}

	public function customFieldConstraints(FormField $field, array $allFieldConstraints) {
		parent::customFieldConstraints($field, $allFieldConstraints);
		$fieldName = $field->getName();

		if ($fieldName == self::RelationshipName) {
			$this->configureUploadField($field);
		}
	}
}