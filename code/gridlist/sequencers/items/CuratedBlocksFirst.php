<?php
namespace Modular\GridList\Sequencers\Items;

class CuratedBlocksFirst extends \Modular\ModelExtension implements \Modular\GridList\Interfaces\ItemsSequencer {
	/**
	 * @param \ArrayList|\DataList $items
	 * @param                      $filters
	 * @param array                $parameters
	 * @return \ArrayList|\DataList
	 */
	public function sequenceGridListItems(&$items, $filters, &$parameters = []) {
		$out = new \ArrayList();

		// push only Block items to output
		foreach ($items as $item) {
			if ($item instanceof \Modular\Blocks\Block) {
				$out->push($item);
			}
		}
		// now push everything which isn't a Block
		foreach ($items as $item) {
			if (!$item instanceof \Modular\Blocks\Block) {
				$out->push($item);
			}
		}
		$items = $out;
	}

}