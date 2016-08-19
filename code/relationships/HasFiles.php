<?php
namespace Modular\Relationships;

use Modular\upload;
use SS_List;

/**
 * @method SS_List Links
 */
class HasFiles extends ManyMany {
	use upload;

	const RelationshipName = 'Files';
	const RelatedClassName = 'File';

	private static $allowed_files = 'documents';

	public function cmsFields() {
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