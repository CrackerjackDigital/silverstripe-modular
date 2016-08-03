<?php
namespace Modular;

use \ArrayList;
use \Image;
use \FormField;

/**
 * HasAudioField
 *
 * @method Media
 */
class HasAudioField extends HasMediaFileField {

	private static $allowed_audio_files = 'audio';

	/**
	 * Return a list with only item being the single related image.
	 *
	 * @return \ArrayList
	 */
	public function Medias() {
		return new ArrayList(array_filter([$this->Audio()]));
	}

	/**
	 * Return the single related image
	 *
	 * @return Image|null
	 */
	public function Audio() {
		return $this()->Media();
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
			$this->configureUploadField($field, 'allowed_audio_files');
		}

	}
}