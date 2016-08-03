<?php
namespace Modular;

abstract class HasMediaFileField extends HasUploadedFileField {
	const RelationshipName = 'Media';
	const UploadFieldName  = 'MediaID';      // keep in sync with RelationshipName
	// override in concrete class e.g. 'audio' or 'video'
	const UploadFolderName = 'media';
	const UploadedFileOption = 'UploadedFile';

	private static $has_one = [
		self::RelationshipName => 'File'
	];

	public static function field_option() {
		return [static::UploadFieldName=> self::UploadedFileOption];
	}

}