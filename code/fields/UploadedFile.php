<?php
namespace Modular\Fields;

abstract class UploadedFile extends Field {
	const RelationshipName = '';
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