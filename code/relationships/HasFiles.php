<?php
namespace Modular\Relationships;

use Modular\Traits\upload;
use SS_List;

/**
 * @method SS_List Links
 */
class HasFiles extends HasManyMany {
	use upload;

	const RelationshipName = 'Files';
	const RelatedClassName = 'File';

	private static $allowed_files = 'download';

	public function cmsFields($mode = null) {
		return [
			new \UploadField(
				static::RelationshipName
			)
		];
	}

	public function customFieldConstraints(\FormField $field, array $allFieldConstraints) {
		if ($field->getName() == static::RelationshipName) {
			$this->configureUploadField($field);
		}
	}

}