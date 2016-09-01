<?php
namespace Modular\Blocks;
/**
 * LinkedPage
 *
 * @package Modular\Blocks
 * @method \Page InternalLink from \Modular\Fields\InternalLink field.
 */
class LinkedPage extends Block {
	public function Page() {
		return $this->InternalLink();
	}
}