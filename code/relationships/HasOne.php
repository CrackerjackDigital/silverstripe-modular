<?php
namespace Modular\Relationships;

use Modular\Fields\Field;

class HasOne extends Field {
	const RelationshipName = '';
	const RelatedClassName = '';

	private static $tab_name = 'Root.Main';

	public function extraStatics($class = null, $extension = null) {
		$parent = parent::extraStatics($class, $extension);

		return array_merge_recursive(
			$parent,
			[
				'has_one' => [
					static::RelationshipName => static::RelatedClassName
				]
			]
		);
	}

	public function onAfterPublish() {
		/** @var \File|\Versioned $file */
		if ($file = $this->{static::RelationshipName}()) {
			if ($file->hasExtension('Versioned')) {
				$file->publish('Stage', 'Live', false);
			}
		}
	}

}