<?php
namespace Modular\Blocks;

class Promo extends Block {
	private static $db = [
		"BackgroundColor" => "Enum('white-bg,brown-bg','white-bg')",
	];

	private static $allowed_files = 'image';

	public function Image() {
		return parent::Image();
	}

	public function allowedFileTypes($configVarName = 'allowed_files') {
		return $this->config()->get($configVarName);
	}
}