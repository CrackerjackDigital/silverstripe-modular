<?php
namespace Modular\GridList;

class SearchConstraints extends Constraints {

	/**
	 * @return string
	 */
	public function fullTextTerms() {
		return $this->getVar(self::FullTextGetVar);
	}

	/**
	 * Return query string suitable for use to link to Search page
	 *
	 * @param array $params
	 * @return string
	 */
	public function buildQueryString($params = []) {
		return parent::buildQueryString(
			array_merge(
				[
					self::FullTextGetVar     => $this->fullTextTerms(),
					self::KeywordsGetVar     => implode(',', $this->keywordIDs()),
					self::AuthorGetVar       => $this->author(),
					self::ArticleTypesGetVar => $this->articleTypes(),
				],
				$params ?: []
			)
		);
	}
}