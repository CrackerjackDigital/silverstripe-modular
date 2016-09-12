<?php
namespace Modular\Relationships;

use Modular\Fields\ModelTag;
use Modular\Model;

/**
 * RelatedPages
 *
 * @package Modular\Relationships
 * @method RelatedPages
 */
abstract class HasRelatedPages extends HasManyMany {
	private static $multiple_select = true;

	private static $cms_tab_name = 'Root.RelatedPages';

	private static $sortable = false;

	/**
	 * Given an array of page model-tags e.g. 'firth', 'people' etc add them as related pages to the extended model if that relationship doesn't already exist.
	 * Does not clear existing relationships.
	 *
	 * @param $modelTags
	 * @return array of pages added to the model
	 * @throws \Exception
	 */
	public function addRelatedPagesByPageModelTag($modelTags) {
		/** @var Model $model */
		$model = $this();
		// keep track of all the pages which are added
		$handled = [];

		/** @var \SS_List $existing */
		$existing = $model->{static::relationship_name()}();

		foreach ($modelTags as $maybeRelationshipTag) {
			// check if we can find the page to relate by it's model tag
			if ($page = \Page::get()->filter(ModelTag::SingleFieldName, $maybeRelationshipTag)->first()) {
				// add it, ManyManyList should prevent duplicates
				$existing->add($page);
				$handled[] = $page;
			}
		}
		return $handled;
	}
}