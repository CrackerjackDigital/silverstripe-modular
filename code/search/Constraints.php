<?php
namespace Modular\Search;

class Constraints extends \Modular\GridList\Filters {
	const FullTextVar = 'q';
	const TagsVar = 'tags';

	private static $params = [
		self::FullTextVar,
	    self::TagsVar
	];

}