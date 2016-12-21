<?php
namespace Modular\Fields;

use FormField;
use Modular\Relationships\HasOne;
use Modular\Relationships\HasManyMany;
use Modular\upload;
use UploadField;

class File extends HasOne {
	use upload;

	const RelationshipName        = 'File';
	const RelatedClassName        = 'File';
	const DefaultUploadFolderName = 'files';

	// if an array then file extensions, if a string then a category e.g. 'video'

	private static $allowed_files = 'download';

	private static $tab_name = 'Root.Files';

	// folder directly under '/assets'
	private static $base_upload_folder = '';

	// this will be appended to 'base_upload_folder'
	private static $upload_folder = self::DefaultUploadFolderName;

	// allow existing files to be attached in CMS by default.
	private static $allow_attach_existing = true;

	public function cmsFields() {
		return [
			$this->makeUploadField(static::field_name()),
		];
	}

	/**
	 * Files are always without ID as use UploadField which breaks convention.
	 *
	 * @param string $suffix
	 * @return string
	 */
	public static function field_name($suffix = '') {
		return static::RelationshipName . $suffix;
	}

	public function allowAttachExisting() {
		return static::config()->get('allow_attach_existing');
	}

	public static function allowed_files_config_var() {
		return 'allowed_files';
	}

	public function customFieldConstraints(FormField $field, array $allFieldConstraints) {
		$fieldName = $field->getName();
		/** @var UploadField $field */
		if ($fieldName == static::field_name()) {
			$this->configureUploadField($field, static::allowed_files_config_var());
		}
	}

	/**
	 * If file is versioned we need to publish it also.
	 */
	public function onAfterPublish() {
		/** @var \File|\Versioned $file */
		if ($file = $this()->{static::relationship_name()}()) {
			if ($file->hasExtension('Versioned')) {
				$file->publish('Stage', 'Live', false);
			}
		}
	}

}