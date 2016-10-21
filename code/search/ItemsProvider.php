<?php
namespace Modular\Search;

use Modular\Fields\Field;
use Modular\Fields\ModelTag;
use Modular\Models\Tag;
use Modular\reflection;

class ItemsProvider extends Field implements \Modular\GridList\Interfaces\ItemsProvider {
	use reflection;

	const SingleFieldName   = 'ProvideFulltextSearch';
	const SingleFieldSchema = 'Boolean';

	private static $search_classes = [
		# at least in config, alse add Modular\Search\ModelExtension to the class itselg
		# 'SiteTree',
		# 'File',
	];

	public function provideGridListItems() {
		$results = new \ArrayList();

		/** @var Service $service */
		$service = \Injector::inst()->get('SearchService');

		$searchClasses = $this()->config()->get('search_classes') ?: [];

		foreach ($searchClasses as $className) {
			$filter = $service->Filters()->filter($className, Constraints::FullTextVar, ModelExtension::SearchIndex);
			if ($filter) {
				// this is a list of e.g. pages, blocks, we next need to
				// ask each page/block for it's actual hits
				// e.g. ask blocks for their pages
				$intermediates = \DataObject::get($className)
					->filter($filter);

				/** @var ModelExtension|\DataObject $intermediate */
				foreach ($intermediates as $intermediate) {
					if ($intermediate->hasMethod('SearchTargets')) {

						$results->merge($intermediate->SearchTargets());

					}
				}
			}
		}
		$allTags = Tag::get();

		if ($tags = array_filter(explode(',', $service->constraint(Constraints::TagsVar)))) {
			foreach ($tags as $tag) {
				if ($tag = $allTags->find(ModelTag::field_name(), $tag)) {
					$results->merge($tag->RelatedByClassName('*Page'));
				}
			}
		}

		return $results;
	}
}