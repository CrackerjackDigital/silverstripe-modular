<?php
namespace Modular\Fields;

use ArrayList;

/**
 * HasVideoField
 *
 * @method Media
 */
class Video extends Media {
	const DefaultUploadFolderName = 'video';

	private static $allowed_video_files = 'mov';

	private static $upload_folder = self::DefaultUploadFolderName;

	/**
	 * Return a list with only item being the single related image.
	 *
	 * @return \ArrayList
	 */
	public function Medias() {
		return new ArrayList(array_filter([$this->Video()]));
	}

	/**
	 * Return the single related image
	 *
	 * @return File|null
	 */
	public function Video() {
		return $this()->Media();
	}

	public static function allowed_files() {
		return 'allowed_video_files';
	}

}