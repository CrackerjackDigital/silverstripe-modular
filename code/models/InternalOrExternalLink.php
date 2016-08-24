<?php
namespace Modular\Models;

use Modular\Model;

class InternalOrExternalLink extends Model {
	const SortFieldName    = 'Sort';
	const RelationshipName = 'LinksBlock';
	const RelatedClassName = 'Modular\Blocks\Block';

	private static $db = [
		self::SortFieldName => 'Int',
	];
	private static $has_one = [
		self::RelationshipName => self::RelatedClassName,
	];
	private static $summary_fields = [
		'Title'        => 'Title',
		'ResolvedLink' => 'Link',
	];

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->replaceField(self::SortFieldName, new \HiddenField(self::SortFieldName));
		return $fields;
	}
}