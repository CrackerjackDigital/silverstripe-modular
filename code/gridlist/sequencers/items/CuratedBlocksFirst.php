<?php
namespace Modular\GridList\Sequencers\Items;

class CuratedBlocksFirst extends \Modular\ModelExtension implements \Modular\GridList\Interfaces\ItemsSequencer {
	/**
	 * @param \ArrayList|\DataList $groups
	 * @param                      $filters
	 * @param array                $parameters
	 * @return \ArrayList|\DataList
	 */
	public function sequenceGridListItems(&$groups, $filters, &$parameters = []) {
		$out = new \ArrayList();

		// push only Block items to output
		foreach ($groups as $item) {
			if ($item instanceof \Modular\Blocks\Block) {
				$out->push($item);
			}
		}
		// now push everything which isn't a Block
		foreach ($groups as $item) {
			if (!$item instanceof \Modular\Blocks\Block) {
				$out->push($item);
			}
		}
		$groups = $out;
	}

}