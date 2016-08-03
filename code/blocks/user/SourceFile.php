<?php
namespace Modular\Blocks;

class SourceFileBlock extends FileBlock {
	private static $allowed_files = 'pdf';

	private static $upload_folder = 'sources';

	public function DisplayInSidebar() {
		return true;
	}
	public function DisplayInContent() {
		return false;
	}

}