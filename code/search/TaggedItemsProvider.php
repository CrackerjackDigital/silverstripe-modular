<?php
namespace Modular\Search;

use Modular\Fields\Field;
use Modular\Fields\ModelTag;
use Modular\GridList\Interfaces\ItemsProvider;
use Modular\ModelExtension;
use Modular\Models\Tag;
use Modular\reflection;

/**
 * Provides items which match 'tags' query paramter as ModelTags - tags can be a csv list of ModelTags.
 *
 * @package Modular\Search
 */
class TaggedItemsProvider extends ModelExtension implements ItemsProvider {
	// only classes matching here by ModelTag.relatedByClassName will be included
	private static $search_classes = [
		# at least in config, alse add Modular\Search\ModelExtension to the class itselg
		# 'SiteTree',
		# 'File',
	];

	public function provideGridListItems() {
		$results = new \ArrayList();

		/** @var Service $service */
		$service = \Injector::inst()->get('SearchService');

		if ($tags = array_filter(explode(',', $service->constraint(Constraints::TagsVar)))) {
			$allTags = Tag::get();
			$searchClasses = $this()->config()->get('search_classes');

			foreach ($tags as $tag) {
				/** @var ModelTag $tag */
				if ($tag = $allTags->find(ModelTag::field_name(), $tag)) {
					// merge all related classes that end in 'Page' for this tag
					$results->merge($tag->relatedByClassName($searchClasses));
				}
			}
		}
		return $results;
	}
}