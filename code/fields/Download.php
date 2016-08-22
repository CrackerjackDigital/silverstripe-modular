<?php
namespace Modular\Fields;

class Download extends File {
	const RelationshipName        = 'Download';
	const RelatedClassName        = 'File';
	const DefaultUploadFolderName = 'downloads';

	// if an array then file extensions, if a string then a category e.g. 'video', if a csv

	private static $allowed_download_files = 'download';

	private static $tab_name = 'Root.Downloads';

	private static $upload_folder = self::DefaultUploadFolderName;


	public function Files() {
		return new \ArrayList(array_filter($this->{static::RelationshipName}()));
	}

	public static function allowed_files() {
		return 'allowed_download_files';
	}

}