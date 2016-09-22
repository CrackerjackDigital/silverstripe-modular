<?php
namespace Modular\GridList\Providers\Items;

use Modular\Fields\Field;
use Modular\GridList\Interfaces\ItemsProvider;
use Modular\Relationships\HasRelatedPages;

/**
 * Provide all pages which have been related to this page via the 'RelatedPages' tab.
 * Adds a field which enables this to be enabled/disabled in CMS.
 *
 * @package Modular\GridList\Providers\Items
 */
class RelatedPages extends Field implements ItemsProvider {
	const SingleFieldName   = 'ProvideRelatedPages';
	const SingleFieldSchema = 'Boolean';

	private static $defaults = [
		self::SingleFieldName => true
	];

	/**
	 * Add implementors token which is csv of 'nice' names of implementors of HasRelatedPages relationship.
	 * @return mixed
	 */
	public function fieldDecorationTokens() {
		$implementors = HasRelatedPages::implementors();
		$titles = [];
		/** @var \Page $page */
		$page = $this();

		foreach ($implementors as $className => $title) {
			$relationshipName = $className::relationship_name();

			if ($page->hasRelationship($relationshipName)) {
				$titles[] = $title;
			}
		}
		return array_merge(
			parent::fieldDecorationTokens(),
			[
				'implementors' => implode(', ', $titles ?: [ 'None found on this page type ' ])
			]
		);
	}

	/**
	 * Use the 'related' method to return related pages.
	 *
	 * @return mixed
	 */
	public function provideGridListItems() {
		if ($this()->{self::SingleFieldName}) {
			if ($page = \Director::get_current_page()) {
				$items = new \ArrayList();

				// iterate through children of 'HasRelatedPages', eg 'BusinessPages', 'DivisionPages' etc
				foreach (HasRelatedPages::implementors() as $className => $title) {
					if ($page->hasExtension($className)) {
						// get all the related e.g. country pages to this page via the 'RelatedCountries' back relationship
						$items->merge(
							$page->{$className::relationship_name()}()
						);
					}
				}
				return $items;
			}
		}
	}

}