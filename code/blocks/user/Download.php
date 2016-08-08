<?php
namespace Modular\Blocks;

class Download extends FileBlock {
	private static $allowed_files = 'download';

	private static $upload_folder = 'downloads';

	public function DisplayInSidebar() {
		return true;
	}

	public function DisplayInContent() {
		return false;
	}
}