<?php
namespace Modular\Search;

use Modular\Fields\Field;
use Modular\reflection;

class ItemsProvider extends Field implements \Modular\GridList\Interfaces\ItemsProvider {
	use reflection;

	const SingleFieldName   = 'ProvideFulltextSearch';
	const SingleFieldSchema = 'Boolean';

	const DefaultSearchIndex = 'FulltextSearchFields';

	private static $search_classes = [
		# at least in config, alse add Modular\Search\ModelExtension to the class itselg
		# 'SiteTree',
		# 'File',
	];

	private static $search_index = self::DefaultSearchIndex;

	public function provideGridListItems() {
		$searchIndex = ModelExtension::SearchIndex;

		/** @var \GridListService $service */
		$service = \Injector::inst()->get('GridListSearchService');

		$fulltext = $service->constraint(Constraints::FullTextVar);

		$results = new \ArrayList();

		$searchClasses = $this()->config()->get('search_classes') ?: [];
		
		foreach ($searchClasses as $className) {
			if ($indexes = \Config::inst()->get($className, 'indexes')) {
				if (isset($indexes[$searchIndex])) {
					// this is a list of e.g. pages, blocks, we next need to
					// ask each page/block for it's actual hits
					// e.g. ask blocks for their pages
					$intermediates = \DataObject::get($className)
						->filter("$searchIndex:fulltext", $fulltext);

					foreach ($intermediates as $intermediate) {
						$results->merge($intermediate->SearchTargets());
					}
					$count = $results->count();
				}
			}
		}
		return $results;
	}
}