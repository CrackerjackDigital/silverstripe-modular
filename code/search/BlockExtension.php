<?php
namespace Modular\Search;

class BlockExtension extends ModelExtension {
	private static $searchable_fields = [
		'Content' => 'PartialMatchFilter',
	];

	/**
	 * Search results for a Block are the pages which have this block.
	 * 
	 * @return \SS_List
	 */
	public function SearchTargets() {
		return $this()->Pages();
	}
}