<?php
namespace Modular\Blocks;

use Modular\Interfaces\Imagery;

class ImageGallery extends Block implements Imagery {

	private static $allowed_files = 'image';

	public function Images() {
		return parent::Images();
	}

	public function Image() {
		return $this->Images()->first();
	}

	public function allowedFileTypes($configVarName = 'allowed_files') {
		return $this->config()->get($configVarName);
	}

}