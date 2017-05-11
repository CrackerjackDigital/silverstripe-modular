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
	 * Check if the model exhibiting the extension (or the extended mode if an extension) has changed
	 *
	 * @param string $previousFileName
	 * @param string $modifiedField
	 * @param string $hashField
	 *
	 * @return bool
	 */
	public function fileChanged( $previousFileName = '', $modifiedField = FileModifiedStamp::Name, $hashField = FileContentHash::Name) {
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
	 * @param string $previousFileName if not supplied then attempt will be made to get from changed fields on File
	 * @param string $modifiedField    name of field which stores the last modified time of the file, supply empty to skip test
	 * @param string $hashField        name of field which stores the file hash value, supply empty to skip test
	 *
	 * @return bool
	 */
	public static function file_changed( File $file, $previousFileName = '', $modifiedField = FileModifiedStamp::Name, $hashField = FileContentHash::Name) {
		if ( $previousFileName || $file->isChanged( 'Filename' ) ) {
			$previousFileName = $previousFileName ?: $file->getChangedFields()['Filename']['before'];

			if ( $previousFileName != $file->Filename ) {
				return true;
			}
		}

		$fileName = Director::getAbsFile( $file->Filename );
		if ( ! file_exists( $fileName ) ) {
			return true;
		}

		if ( $modifiedField && $file->hasField($modifiedField)) {
			if ( $file->$modifiedField != filemtime( $fileName ) ) {
				return true;
			}
		}
		if ($hashField && $file->hasField($hashField)) {
			if ( $file->$hashField != static::hash_file( $fileName ) ) {
				return true;
			}
		}

		return false;
	}
}