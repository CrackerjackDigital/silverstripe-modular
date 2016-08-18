<?php
namespace Modular\Blocks;

class Files extends Block {
	private static $upload_folder = '';

	private static $allowed_files = '';

	public static function upload_folder() {
		return static::config()->get('upload_folder');
	}

	public function getUploadFolder() {
		return static::upload_folder();
	}

	public function allowedFileTypes($configVarName = 'allowed_files') {
		return $this->config()->get($configVarName);
	}

}