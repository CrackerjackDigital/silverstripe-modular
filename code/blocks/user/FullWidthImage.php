<?php

class FullWidthImageBlock extends BlockModel implements ImageryInterface {

	private static $allowed_files = 'image';

	public function Images() {
		return new ArrayList(array_filter([$this->Image()]));
	}

	public function Image() {
		return parent::Image();
	}

	public function allowedFileTypes($configVarName = 'allowed_files') {
		return $this->config()->get($configVarName);
	}
}