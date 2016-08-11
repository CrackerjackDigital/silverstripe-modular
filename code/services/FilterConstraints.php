<?php
namespace Modular\GridList;

use TaxonomyTerm;

/**
 * Decodes url and get parameters to something the GridListService can use.
 * Saves statefull parameters to the session, keyed off the current url.
 *
 * Expected route is:
 *     'gridlist/$Aspect!/$Category/$Type/$Mode'
 *
 *
 *
 *
 */
class FilterConstraints extends Constraints {
	const ParamListSeperator = ',';
	/**
	 * Return query string suitable for use to link to a filter page
	 *
	 * @param array $params
	 * @return string
	 */
	public function buildQueryString($params = []) {
		return parent::buildQueryString(
			array_merge(
				[
				],
				$params ?: []
			)
		);
	}

	/**
	 * Return a taxonomy term ID for the passed URLSegment if a string, or the ID if numeric. Checks ot
	 * make sure that one exists by URLSegment or by ID.
	 *
	 * @param $urlParam
	 * @return int|null
	 */
	protected function taxonomyIDFromURLParam($urlParam) {
		$id = null;
		if ($idOrURLSegment = $this->urlParam($urlParam)) {
			if (is_numeric($idOrURLSegment)) {
				if (1 === TaxonomyTerm::get()->filter('ID', $idOrURLSegment)->count()) {
					$id = $idOrURLSegment;
				}
			} else {
				if ($term = TaxonomyTerm::get()->filter('URLSegment', $idOrURLSegment)->first()) {
					$id = $term->ID;
				}
			}
		}
		return $id;
	}

}
