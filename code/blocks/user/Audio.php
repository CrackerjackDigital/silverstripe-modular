<?php
namespace Modular\Blocks;

use ArrayList;
use Modular\Fields\Media;

/**
 * AudioBlock
 *
 * @method Audio
 */
class Audio extends File {
	const DefaultUploadFolderName = 'audio';

	private static $allowed_files = 'audio';

	private static $upload_folder = self::DefaultUploadFolderName;

	public function Audios() {
		return new ArrayList(array_filter([$this->Audio()]));
	}

}