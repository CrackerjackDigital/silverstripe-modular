<?php
namespace Modular\Search;

class Constraints extends \Modular\GridList\Constraints {
	const FullTextVar = 'q';
	const TagsVar = 'tags';

	private static $params = [
		self::FullTextVar,
	    self::TagsVar
	];

	/**
	 * Return a filter (array) suitable for the class provided using the term provided. Checks to
	 * see if the class has an index named that added by the Search\ModelExtension (e.g. 'FulltextSearchIndex')
	 *
	 * @param string $className
	 * @param string $searchText
	 * @param string $searchIndex added by enabling fulltext search, e.g. 'FulltextSearchIndex'
	 * @return array
	 */
	public function searchFilter($className, $searchText, $searchIndex) {
		if ($fulltext = trim($this->constraint($searchText))) {
			if ($indexes = \Config::inst()->get($className, 'indexes')) {
				if (isset($indexes[ $searchIndex ])) {
					return array_filter([
						"$searchIndex:fulltext" => $fulltext
					]);
				}
			}
		}
		return [];
	}
}