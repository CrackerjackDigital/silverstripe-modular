<?php
namespace Modular\Fields;

use TextField;

class Title extends Fields {
	const TitleFieldName = 'Title';

	private static $db = [
		self::TitleFieldName => 'Varchar(255)'
	];

	private static $summary_fields = [
		self::TitleFieldName => 'Title'
	];

	public function updateSummaryFields(&$fields) {
		$fields[self::TitleFieldName] = $this->translatedMessage(self::TitleFieldName, 'Label', 'Title');
	}

	public function cmsFields() {
		return [
			new TextField(self::TitleFieldName)
		];
	}
}