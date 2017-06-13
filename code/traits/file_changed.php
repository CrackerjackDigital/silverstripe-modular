<?php

namespace Modular\Traits;

use Director;
use File;
use Modular\Fields\FileContentHash;
use Modular\Fields\FileModifiedStamp;

trait file_changed {
	/**
	 * Return the physical File, if exhibited on a Model should return $this, if an extension should return owner.
	 *
	 * @return File
	 */
	abstract public function model();

	/**
	 * Return hash of provided file name. Some reason can't have abstract statics in traits however declaring here for informative reasons.
	 *
	 * @param string $fileName
	 *
	 * @return string hash of file content
	 */
	// abstract static public function hash_file($fileName = '');

	/**
	 * Check if the extended model (probably a File) has changed
	 *
	 * @param bool   $filesOnly don't do folders just files if true, otherwise both
	 * @param string $previousFileName
	 * @param string $modifiedField
	 * @param string $hashField
	 *
	 * @return bool
	 */
	public function fileChanged( $filesOnly, $previousFileName = '', $modifiedField = FileModifiedStamp::Name, $hashField = FileContentHash::Name ) {
		if ($filesOnly && ($this->model()->ClassName == \Folder::class)) {
			return false;
		}
		return static::file_changed( $this->model(), $previousFileName, $modifiedField, $hashField );
	}

	/**
	 * Check if the file has changed via (in order of specificity):
	 *
	 *  - File Name
	 *  - Modified Time
	 *  - Hash
	 *
	 * This should be called in onBeforeWrite only so the 'original' values are available to compare to, or a previous file name supplied otherwise.
	 *
	 * @param File   $file
	 *
	 * @param string $previousFileName   if not supplied then attempt will be made to get from changed fields on File
	 * @param string $modifiedStampField name of field which stores the last modified time of the file, supply empty to skip test
	 * @param string $hashField          name of field which stores the file hash value, supply empty to skip test
	 *
	 * @return bool
	 */
	public static function file_changed( File $file, $previousFileName = '', $modifiedStampField = FileModifiedStamp::Name, $hashField = FileContentHash::Name ) {
		if ( $previousFileName || $file->isChanged( 'Filename' ) ) {
			$previousFileName = $previousFileName ?: $file->getChangedFields()['Filename']['before'];

			if ( $previousFileName != $file->Filename ) {
				return true;
			}
		}

		$fileName = Director::getAbsFile( $file->Filename );
		if ( $modifiedStampField && $file->hasField( $modifiedStampField ) ) {
			if ( $file->$modifiedStampField != filemtime( $fileName ) ) {
				return true;
			}
		}
		if ( $hashField && $file->hasField( $hashField ) ) {
			if ( $file->$hashField != static::hash_file( $fileName ) ) {
				return true;
			}
		}

		return false;
	}
}