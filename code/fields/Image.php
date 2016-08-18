<?php
namespace Modular\Fields;

use ArrayList;
use FormField;
use Modular\Interfaces\Imagery;

class Image extends File implements Imagery {
	const RelationshipName        = 'Image';

	private static $base_upload_folder = 'images';

	private static $allowed_files = 'image';

	/**
	 * Return a list with only item being the single related image.
	 * @return \ArrayList
	 */
	public function Images() {
		return new ArrayList(array_filter([$this()->{self::RelationshipName}()]));
	}

	/**
	 * Return the single related image, shouldn't really get here as the extended model's field accessor should be called first.
	 * @return Image|null
	 */
	public function Image() {
		return $this()->{self::RelationshipName}();
	}

}