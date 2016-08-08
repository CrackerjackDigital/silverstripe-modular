<?php
namespace Modular\Blocks;

use ArrayList;
/**
 * AudioBlock
 *
 * @method Audio
 */
class Audio extends File {

	private static $allowed_files = 'audio';

	public function Audios() {
		return new ArrayList(array_filter([$this->Audio()]));
	}

}