<?php
namespace Modular\Blocks;
use Modular\Fields\ExternalLink;
use Modular\Fields\InternalLink;
use ArrayList;
use Modular\Fields\Media;

/**
 * VideoBlock
 *
 * @method File Video provided by HasVideoField
 * @method bool IsExternalLink provided by InternalOrExternalLink
 * @method bool IsInternalLink provided by InternalOrExternalLink
 */
class Video extends File {
	const DefaultUploadFolderName = 'video';

	private static $allowed_files = 'mov';

	private static $upload_folder = self::DefaultUploadFolderName;

	/**
	 * Link which player should use (e.g. in iframe src attribute).
	 * @return mixed
	 */
	public function PlayerLink() {
		if ($this->IsExternalLink()) {
			return $this->{ExternalLink::SingleFieldName};
		} elseif ($this->IsInternalLink()) {
			if ($target = $this->{InternalLink::relationship_name()}()) {
				return $target->Link();
			}
		}
	}

	public function LinkType() {
		return 'Video';
	}

	/**
	 * Return text to show in a link to this block (or more likely a link this block contains, such as a File via the HasLinks interface).
	 *
	 * Gets different text either 'ExternalLinkText' or 'InternalLinkText' or 'LinkText' if they are not set.
	 *
	 * @return mixed
	 */
	public function LinkText() {
		$blockClass = get_class($this);
		$type = $this->IsExternalLink() ? 'ExternalLinkText' : 'InternalLinkText';
		return _t("$blockClass.$type", _t("$blockClass.LinkText", 'More'));
	}

	/**
	 * Returns a list of video's with the one associated video as the first item.
	 *
	 * @return \ArrayList
	 */
	public function Videos() {
		return new ArrayList(array_filter([$this->Video()]));
	}

	public function IsVimeo() {
		if ($this->IsExternalLink()) {
			return false !== strpos($this->{ExternalLink::SingleFieldName}, 'vimeo');
		}
	}

	public function IsYouTube() {
		if ($this->IsExternalLink()) {
			return false !== strpos($this->{ExternalLink::SingleFieldName}, 'youtube');
		}
	}
}