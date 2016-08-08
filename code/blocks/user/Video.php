<?php
namespace Modular\Blocks;
use Modular\Fields\ExternalLink;
use Modular\Fields\InternalLink;
use ArrayList;

/**
 * VideoBlock
 *
 * @method File Video provided by HasVideoField
 * @method bool IsExternalLink provided by InternalOrExternalLink
 * @method bool IsInternalLink provided by InternalOrExternalLink
 */
class Video extends File {
	private static $allowed_files = 'mov';

	/**
	 * Link which player should use (e.g. in iframe src attribute).
	 * @return mixed
	 */
	public function PlayerLink() {
		if ($this->IsExternalLink()) {
			return $this->{ExternalLink::ExternalLinkFieldName};
		} elseif ($this->IsInternalLink()) {
			if ($target = $this->{InternalLink::RelationshipName}()) {
				return $target->Link();
			}
		}
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
			return false !== strpos($this->{ExternalLink::ExternalLinkFieldName}, 'vimeo');
		}
	}

	public function IsYouTube() {
		if ($this->IsExternalLink()) {
			return false !== strpos($this->{ExternalLink::ExternalLinkFieldName}, 'youtube');
		}
	}
}