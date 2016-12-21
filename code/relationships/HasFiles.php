<?php
namespace Modular\Relationships;

use Modular\upload;
use SS_List;

/**
 * @method SS_List Links
 */
class HasFiles extends HasManyMany {
	use upload;

	const RelationshipName = 'Files';
	const RelatedClassName = 'File';

	private static $allowed_files = 'download';

	private static $allow_attach_existing = true;

	private static $many_many_extraFields = [
		'Files' => [
			'SortOrder' => 'Int',
		],
	];

	public function cmsFields() {
		return [
			new \SortableUploadField(
				static::RelationshipName
			),
		];
	}

	public function customFieldConstraints(\FormField $field, array $allFieldConstraints) {
		if ($field->getName() == static::RelationshipName) {
			$this->configureUploadField($field);
		}
	}

	public function allowAttachExisting() {
		return static::config()->get('allow_attach_existing');
	}

}