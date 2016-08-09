<?php
namespace Modular\Fields;

abstract class UploadedFile extends Fields {
	const RelationshipName = '';
	const UploadFieldName  = '';      // keep in sync with RelationshipName, ie '<RelationshipName>ID' for has_one field name
	const UploadFolderName = 'uploads';

	// has_one relationship goes on concrete class to pick up static RelationshipName, File model etc

	public function onAfterPublish() {
		/** @var \File|\Versioned $file */
		foreach ($this->{static::RelationshipName}() as $file) {
			if ($file && $file->hasExtension('Versioned')) {
				$file->publish('Stage', 'Live', false);
			}
		}
	}
}