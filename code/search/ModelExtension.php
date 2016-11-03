<?php
namespace Modular\Search;

/**
 * ModelExtension
 *
 * @package Modular\Search
 */
abstract class ModelExtension extends \Modular\ModelExtension {
	const SearchIndex = 'FulltextSearchIndex';

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
		$fulltextFields = array_keys(
			array_filter(
				static::config()->get('fulltext_fields') ?: []
			)
		);
		$statics = parent::extraStatics($class, $extension) ?: [];
		if ($fulltextFields) {
			$statics = array_merge(
				$statics,
				[
					'create_table_options' => [
						'MySQLDatabase' => 'ENGINE=MyISAM'
					],
					'indexes'              => [
						static::SearchIndex => [
							'type'  => 'fulltext',
							'name'  => static::SearchIndex,
							'value' => implode(',', $fulltextFields),
						],
					]
				]
			);
		}
		return $statics;
	}
}
