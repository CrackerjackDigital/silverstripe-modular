<?php
namespace Modular\Search;

use Modular\GridList\Interfaces\ItemsProvider;

/**
 * Provides items which match 'tags' query parameter as ModelTags - tags can be a csv list of ModelTags.
 *
 * @package Modular\Search
 */
class TaggedItemsProvider extends \Modular\ModelExtension implements ItemsProvider {
	// only classes matching here by ModelTag.relatedByClassName will be included
	// e.g. '*Page' will be all classes ending in 'Page'
	private static $search_classes = [
		# 'SiteTree',
		# 'File',
	];

	public function provideGridListItems() {
		$results = new \ArrayList();

		/** @var Service $service */
		$service = Service::factory();

		if ($tags = array_filter(explode(',', $service->constraint(Constraints::TagsVar)))) {
			$searchClasses = $this->config()->get('search_classes');

			foreach ($searchClasses as $className) {
				$forTag = \DataObject::get($className)->filter('Tags.ModelTag', $tags);
				$results->merge($forTag);
			}
		}
		return $results;
	}
}