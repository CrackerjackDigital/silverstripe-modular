<?php
namespace Modular\Blocks;

use Modular\Interfaces\HasLinks;

class Files extends Block implements HasLinks {
	private static $upload_folder = '';

	private static $allowed_files = '';

	/**
	 * Return list of links information from Files relationship. See HasLinks for more info.
	 * @return \ArrayList
	 */
	public function LinkInfo() {
		$links = new \ArrayList();

		/** @var \File $file */
		foreach ($this->Files() as $file) {
			$links->push(new \ArrayData([
				'Link'     => $file->Link(),
				'Title'    => $file->Title,
				'LinkType' => 'File',
			    'LinkText' => $this->LinkText()
			]));
		}
		return $links;
	}

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