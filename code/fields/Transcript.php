<?php
namespace Modular\Fields;

use ArrayList;
use FormField;
use Modular\Behaviours\MediaLinkType;

/**
 * HasTranscriptField
 *
 * @method Transcript
 */

class Transcript extends Field {
	const RelationshipName = 'Transcript';
	const UploadFieldName  = 'TranscriptID';      // keep in sync with RelationshipName
	const UploadFolderName = 'transcripts';

	private static $has_one = [
		self::RelationshipName => 'File'
	];

	private static $allowed_transcript_files = 'doc';

	/**
	 * Return a list with only item being the single related transcript.
	 *
	 * @return \ArrayList
	 */
	public function Transcripts() {
		return new ArrayList(array_filter([$this->Transcript()]));
	}


	/**
	 * Adds a single Transcript single-selection UploadField
	 *
	 * @return array
	 */
	public function cmsFields() {
		return [
			$this->makeUploadField(static::RelationshipName)
		];
	}

	public function customFieldConstraints(FormField $field, array $allFieldConstraints) {
		parent::customFieldConstraints($field, $allFieldConstraints);
		$fieldName = $field->getName();

		if ($fieldName == self::RelationshipName) {
			$this->configureUploadField($field, 'allowed_transcript_files');
			$field->hideUnless(MediaLinkType::MediaLinkTypeFieldName)->isEqualTo('EmbedCode');
		}
	}

	public function onAfterPublish() {
		if ($transcript = $this->Transcript()) {
			if ($transcript->hasExtension('Versioned')) {
				$transcript->publish('Stage', 'Live', false);
			}
		}
	}
}