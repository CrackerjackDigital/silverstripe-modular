<?php

/**
 * VideoBlock
 *
 * @method File Video provided by HasVideoField
 * @method bool IsExternalLink provided by InternalOrExternalLinkBehaviour
 * @method bool IsInternalLink provided by InternalOrExternalLinkBehaviour
 */
class VideoBlock extends FileBlock {
	private static $allowed_files = 'mov';

	/**
	 * Link which player should use (e.g. in iframe src attribute).
	 * @return mixed
	 */
	public function PlayerLink() {
		if ($this->IsExternalLink()) {
			return $this->{HasExternalLinkField::ExternalLinkFieldName};
		} elseif ($this->IsInternalLink()) {
			if ($target = $this->{HasInternalLinkField::RelationshipName}()) {
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
			return false !== strpos($this->{HasExternalLinkField::ExternalLinkFieldName}, 'vimeo');
		}
	}

	public function IsYouTube() {
		if ($this->IsExternalLink()) {
			return false !== strpos($this->{HasExternalLinkField::ExternalLinkFieldName}, 'youtube');
		}
	}
}