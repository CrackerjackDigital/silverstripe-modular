<?php
namespace Modular\Relationships;

use Modular\Fields\Fields;
use UploadField;
use FormField;

class Download extends Fields {
	const RelationshipName = 'Download';
	const UploadFolderName = 'downloads';

	private static $has_one = [
		self::RelationshipName => 'File'
	];

	// if an array then file extensions, if a string then a category e.g. 'video'
	private static $allowed_files = 'download';

	private static $tab_name = 'Root.Main';

	private static $upload_folder = self::UploadFolderName;

	public function cmsFields() {
		return [
			new UploadField(
				self::RelationshipName,
				$this->translatedMessage(
					self::RelationshipName,
					'Label',
					'Download'
				)
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