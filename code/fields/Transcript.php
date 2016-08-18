<?php
namespace Modular\Fields;

use ArrayList;
use FormField;
use Modular\Behaviours\MediaLinkType;
use Modular\upload;

/**
 * HasTranscriptField
 *
 * @method Transcript
 */

class Transcript extends File {
	use upload;

	const RelationshipName        = 'Transcript';
	const DefaultUploadFolderName = 'transcripts';

	private static $allowed_transcript_files = 'doc';

	/**
	 * Return a list with only item being the single related transcript.
	 *
	 * @return \ArrayList
	 */
	public function Transcripts() {
		return new ArrayList(array_filter([$this->Transcript()]));
	}

	public static function allowed_files() {
		return 'allowed_transcript_files';
	}
}