<?php
namespace Modular\Fields;

use FormField;

class Logo extends Field {
	const RelationshipName        = 'Logo';
	const UploadFieldName         = 'LogoID';      // keep in sync with RelationshipName
	const DefaultUploadFolderName = 'logos';

	private static $has_one = [
		self::RelationshipName => 'Image'
	];

	private static $allowed_logo_files = 'image';

	/**
	 * Return the single related image, shouldn't really get here as the extended model's field accessor should be called first.
	 *
	 * @return Image|null
	 */
	public function Logo() {
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
			$this->configureUploadField($field, 'allowed_logo_files');
		}
	}
}