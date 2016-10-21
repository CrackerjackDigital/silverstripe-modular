<?php
namespace Modular\Blocks;

use Modular\Interfaces\HasLinks;
use Modular\Models\InternalOrExternalLink;

/**
 * Links
 *
 * @package Modular\Blocks
 * @method \SS_List Links() from HasLinks extension, returns a list of InternalOrExternalLink models
 */
class Links extends Block implements HasLinks {
	/**
	 * Implemented for HasLinks interface,
	 * @return \SS_List of ArrayData objects with Link information.
	 */
	public function LinkInfo() {
		$links = new \ArrayList();

		/** @var InternalOrExternalLink|\Modular\Behaviours\InternalOrExternalLink $link */
		foreach ($this->Links() as $link) {
			$links->push(new \ArrayData([
				'Link' => $link->ResolvedLink(),
				'Title' => $link->Title,
				'LinkType' => $link->LinkType,
			    'LinkText' => $this->LinkText() ?: $link->LinkText()
			]));
		}
		return $links;
	}
}