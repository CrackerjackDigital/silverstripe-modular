<?php
namespace Modular\Search;

use Modular\GridList\Interfaces\ItemsProvider;
/**
 * Provides items which match fulltext parameter 'q'
 *
 * @package Modular\Search
 */
class FulltextProvider extends \Modular\ModelExtension implements ItemsProvider {
	// only classes matched here via ModelTag.relatedByClassName will be returned
	private static $search_classes = [
		# at least in config, alse add Modular\Search\ModelExtension to the class itselg
		# 'SiteTree',
		# 'File',
	];

	public function provideGridListItems() {
		$results = new \ArrayList();

		/** @var Service $service */
		$service = \Injector::inst()->get('SearchService');

		// check something was passed in 'q' parameter up front to skip processing if we can
		if ($service->constraint(Constraints::FullTextVar)) {
			$searchClasses = $this()->config()->get('search_classes') ?: [];

			foreach ($searchClasses as $className) {
				$filter = $service->Filters()->filter($className, Constraints::FullTextVar, \Modular\Search\ModelExtension::SearchIndex);
				if ($filter) {
					$intermediates = \DataObject::get($className)
						->filter($filter);

					/** @var ModelExtension|\DataObject $intermediate */
					foreach ($intermediates as $intermediate) {
						if ($intermediate->hasMethod('SearchTargets')) {

							// merge in what the intermediate object thinks are it's actual targets,
							// e.g. for a ContentBlock this is the Pages which are related to that block
							$results->merge($intermediate->SearchTargets());

						} else {
							// if no search targets nominated then just add the intermediate as it is the target
							$results->push($intermediate);
						}
					}
				}
			}
		}
		return $results;
	}
}