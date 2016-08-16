<?php
namespace Modular\Fields;

use FormField;
use Modular\Interfaces\Imagery;

class Images extends Field implements Imagery {
	const RelationshipName = 'Images';
	const UploadFieldName = 'Images';
	const UploadFolderName = 'images';

	private static $has_many = [
		self::RelationshipName => 'Image'
	];

	private static $allowed_files = 'image';

	/**
	 * Return the list of related images (may be empty), should be satisfied by the model before we get here.
	 *
	 * @return \ArrayList
	 */
	public function Images() {
		return $this()->{self::RelationshipName}();
	}

	/**
	 * Return the first of the related images (may be null).
	 * @return mixed
	 */
	public function Image() {
		return $this->Images()->first();
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
		if ($field->getName() == self::RelationshipName) {
			$this->configureUploadField($field);
		}
	}
}