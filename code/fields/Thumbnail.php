<?php
namespace Modular\Fields;

class Thumbnail extends Image {
	const RelationshipName        = 'Thumbnail';
	const DefaultUploadFolderName = 'thumbnails';

	private static $allowed_thumbnail_files = 'image';

	private static $upload_folder = 'thumbnails';

	/**
	 * Return the single related image, shouldn't really get here as the extended model's field accessor should be called first.
	 *
	 * @return Image|null
	 */
	public function Thumbnail() {
		return $this()->{self::RelationshipName}();
	}

	public function Thumbnails() {
		return new \ArrayList(array_filter([$this->Thumbnail()]));
	}

}