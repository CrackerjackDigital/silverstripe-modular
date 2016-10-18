<?php
namespace Modular\Blocks;

use Modular\Interfaces\HasLinks;

class File extends Block implements HasLinks  {
	// override in concrete class e.g. 'Download' or 'Video'
	const RelationshipName = '';

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

	/**
	 * Return list of link information where the single linked file is the only entry.
	 * @return \ArrayList
	 */
	public function LinkInfo() {
		/** @var \File $file */
		$links = new \ArrayList();

		if ($file = $this->{static::RelationshipName}()) {
			$links->push(
				new \ArrayData([
					'Title' => $file->Title,
				    'Link' => $file->Link(),
				    'LinkType' => $this->LinkType(),
				    'LinkText' => $this->LinkText()
				])
			);
		}
		return $links;
	}

}