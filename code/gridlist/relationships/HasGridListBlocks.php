<?php
namespace Modular\Relationships;

use Modular\GridList\Interfaces\ItemsSequencer;
use Versioned;

/**
 * Add blocks manually to a grid list items at the start.
 *
 * @package Modular\GridList
 */
class HasGridListBlocks extends HasBlocks implements ItemsSequencer {
	const RelationshipName    = 'GridListBlocks';
	const RelatedClassName    = 'Modular\Blocks\Block';
	const GridFieldConfigName = 'Modular\GridField\HasGridListBlocksGridFieldConfig';

	/**
	 * Inserts manually added blocks at front of list.
	 *
	 * @param \ArrayList|\DataList $items
	 * @param                      $filters
	 * @param array                $parameters
	 */
	public function sequenceGridListItems(&$items, $filters, &$parameters = []) {
		$out = new \ArrayList();

		// reverse sort so insertFirst works
		$blocks = $this->related()->Sort('Sort desc');
		foreach ($blocks as $block) {
			$out->push($block);
		}
		foreach ($items as $item) {
			$out->push($item);
		}
		$items = $out;
	}

	/**
	 * If a block no longer has any linked pages or linked grid list blocks then it can be unpublished (deleted from Live)
	 */
	public function onAfterUnpublish() {
		/** @var \DataList $gridListBlocks $blocks */

		if ($blocks = $this->related()) {
			foreach ($blocks as $block) {
				if ($block->hasExtension('Versioned')) {
					if (!$this->hasLinks($block)) {
						$oldMode = Versioned::get_reading_mode();
						Versioned::reading_stage('Live');
						$block->delete();
						\Versioned::set_reading_mode($oldMode);
					}
				}
			}
		}
	}

	/**
	 * Checks if a block has links to a GridList block other than the current block
	 * @param $block
	 * @return bool
	 */
	protected function hasLinks($block) {
		$gridListBlocks = \GridListBlock::get();
		foreach ($gridListBlocks as $gridListBlock) {
			if ($gridListBlock->find('Modular\Blocks\BlockID', $block->ID)) {
				return true;
			}
		}
		return false;
	}
}