<?php

namespace Modular\Models;

class InternalOrExternalLink extends \Modular\Model {
	const SortFieldName = 'Sort';
	const Name          = 'LinksBlock';
	const Schema        = Block::class;

	private static $db = [
		self::SortFieldName => 'Int',
	];
	private static $has_one = [
		self::Name => self::Schema,
	];
	private static $summary_fields = [
		'Title'        => 'Title',
		'ResolvedLink' => 'Link',
	];

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->replaceField( self::SortFieldName, new \HiddenField( self::SortFieldName ) );

		return $fields;
	}

}
