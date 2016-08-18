<?php
namespace Modular\Fields;

use FormField;

class Thumbnail extends Field {
	const RelationshipName = 'Thumbnail';
	const UploadFieldName  = 'ThumbnailID';      // keep in sync with RelationshipName
	const UploadFolderName = 'thumbnails';

	private static $has_one = [
		self::RelationshipName => 'Image'
	];

	private static $allowed_thumbnail_files = 'image';

	/**
	 * Return the single related image, shouldn't really get here as the extended model's field accessor should be called first.
	 *
	 * @return Image|null
	 */
	public function Thumbnail() {
		return $this()->{self::RelationshipName}();
	}

	/**
	 * Adds a single Image single-selection UploadField
	 *
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
			$this->configureUploadField($field, 'allowed_thumbnail_files');
		}
	}
}