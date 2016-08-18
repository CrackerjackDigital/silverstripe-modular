<?php
namespace Modular\Fields;

use FormField;
use Modular\Relationships\HasOne;
use Modular\upload;
use UploadField;

class File extends HasOne {
	use upload;

	const RelationshipName        = 'File';
	const RelatedClassName        = 'File';
	const DefaultUploadFolderName = 'files';
	const UploadFieldName         = '';

	// if an array then file extensions, if a string then a category e.g. 'video'

	private static $allowed_files = 'download';

	private static $tab_name = 'Root.Files';

	private static $base_upload_folder = '';

	private static $upload_folder = self::DefaultUploadFolderName;

	public function cmsFields() {
		return [
			$this->makeUploadField(static::field_name()),
		];
	}

	/**
	 * Files are always without ID ?
	 * @return string
	 */
	public static function field_name() {
		return static::UploadFieldName ?: static::RelationshipName;
	}

	public static function allowed_files() {
		return 'allowed_files';
	}

	public function customFieldConstraints(FormField $field, array $allFieldConstraints) {
		$fieldName = $field->getName();
		/** @var UploadField $field */
		if ($fieldName == static::field_name()) {
			$this->configureUploadField($field, static::allowed_files());
		}
	}

}