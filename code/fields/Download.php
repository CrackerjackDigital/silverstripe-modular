<?php
namespace Modular\Fields;

use FormField;
use UploadField;

class Download extends File {
	const RelationshipName        = 'Download';
	const RelatedClassName        = 'File';
	const DefaultUploadFolderName = 'downloads';

	// if an array then file extensions, if a string then a category e.g. 'video'

	private static $allowed_files = 'download';

	private static $tab_name = 'Root.Main';

	private static $upload_folder = self::DefaultUploadFolderName;

	public function cmsFields() {
		return [
			new UploadField(
				self::RelationshipName
			)
		];
	}

	public function customFieldConstraints(FormField $field, array $allFieldConstraints) {
		$fieldName = $field->getName();
		/** @var UploadField $field */
		if ($fieldName == self::RelationshipName) {
			$this->configureUploadField($field);
		}
	}

}