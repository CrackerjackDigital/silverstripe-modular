<?php
namespace Modular;

/**
 * HasVideoField
 *
 * @method Media
 */
class HasVideoField extends HasUploadedFileField {
	const RelationshipName = 'Media';
	const UploadFieldName  = 'MediaID';      // keep in sync with RelationshipName
	const UploadFolderName = 'video';

	private static $allowed_video_files = 'mov';

	/**
	 * Return a list with only item being the single related image.
	 *
	 * @return \ArrayList
	 */
	public function Medias() {
		return new ArrayList(array_filter([$this->Video()]));
	}

	/**
	 * Return the single related image
	 *
	 * @return File|null
	 */
	public function Video() {
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
			$this->configureUploadField($field, 'allowed_video_files');
		}
	}

}