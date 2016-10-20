<?php
namespace Modular\Search;

/**
 * ModelExtension
 *
 * @package Modular\Search
 */
abstract class ModelExtension extends \Modular\ModelExtension {
	const SearchIndex = 'FulltextSearchIndex';

	private static $create_table_options = array(
		'MySQLDatabase' => 'ENGINE=MyISAM',
	);

	/**
	 * Search results for most Models is the model itself, override if something different.
	 *
	 * @return \ArrayList
	 */
	public function SearchTargets() {
		return new \ArrayList([
			$this()
		]);
	}

	/**
	 * Add full text search field indexes.
	 * @param null $class
	 * @param null $extension
	 * @return array
	 */

	public function extraStatics($class = null, $extension = null) {
		// just get the enabled ones, field names are keys, if value is false then skip it
		$searchableFields = array_keys(
			array_filter(
				static::config()->get('searchable_fields') ?: []
			)
		);
		return array_merge_recursive(
			parent::extraStatics($class, $extension),
			[
				'indexes' => [
					static::SearchIndex => [
						'type'  => 'fulltext',
						'name'  => static::SearchIndex,
						'value' => implode(',', $searchableFields),
					],
				],
			]
		);
	}
}
