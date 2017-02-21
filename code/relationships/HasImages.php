<?php
namespace Modular\Relationships;

use FormField;
use Modular\Interfaces\Imagery;
use Modular\Traits\upload;

class HasImages extends HasManyMany implements Imagery {
	use upload;

	const RelationshipName        = 'Images';
	const RelatedClassName        = 'Image';
	const DefaultUploadFolderName = 'images';

	private static $allowed_image_files = 'image';

	/**
	 * Adds a single Image single-selection UploadField
	 *
	 * @param $mode
	 * @return array
	 * @throws \Exception
	 */
	public function cmsFields($mode = null) {
		return [
			$this->makeUploadField(static::RelationshipName),
		];
	}

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
	 *
	 * @return mixed
	 */
	public function Image() {
		return $this->Images()->first();
	}


	public function customFieldConstraints(FormField $field, array $allFieldConstraints) {
		parent::customFieldConstraints($field, $allFieldConstraints);
		if ($field->getName() == self::RelationshipName) {
			$this->configureUploadField($field, 'allowed_image_files');
		}
	}
}