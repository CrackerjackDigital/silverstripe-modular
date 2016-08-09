<?php
namespace Modular\Behaviours;

use ClassInfo;
use DropdownField;
use FormField;
use Modular\Fields\EmbedCode;
use Modular\Fields\ExternalLink;
use Modular\Fields\Fields;
use Modular\Fields\InternalLink;
use Modular\Relationships\Media;

/**
 * Link type field and logic for a model which has an EmbedCode, InternalLink and ExternalLink fields.
 */
class MediaLinkTypeBehaviour extends Fields {
	const MediaLinkTypeFieldName = 'MediaLinkType';

	private static $enum_values = [
		\Modular\Fields\EmbedCode::EmbedCodeOption,
		\Modular\Relationships\Media::UploadedFileOption,
		\Modular\Fields\ExternalLink::ExternalLinkOption,
	];

	public function IsExternalLink() {
		return $this()->{self::MediaLinkTypeFieldName} == ExternalLink::ExternalLinkOption;
	}

	public function IsInternalLink() {
		return $this()->{self::MediaLinkTypeFieldName} == InternalLink::InternalLinkOption;
	}

	public function IsEmbedCode() {
		return $this()->{self::MediaLinkTypeFieldName} == EmbedCode::EmbedCodeOption;
	}

	public function PlayerLink() {
		// for now the player link is just the internal/external link with no decoration
		return $this->ResolvedLink();
	}

	public function ResolvedLink() {
		if ($this->IsExternalLink()) {
			return $this()->{ExternalLink::ExternalLinkFieldName};
		} elseif ($this->IsInternalLink()) {
			return $this()->{InternalLink::InternalLinkFieldName};
		}
	}

	/**
	 * Return static db enum schema definition for the InternalLink and ExternalLink Option constants.
	 *
	 * @param null $class
	 * @param null $extension
	 * @return array
	 */
	public function extraStatics($class = null, $extension = null) {
		$values = implode(',', $this->config()->get('enum_values'));
		return array_merge_recursive(
			parent::extraStatics($class, $extension) ?: [],
			[
				'db' => [
					self::MediaLinkTypeFieldName => 'enum("' . $values . '")',
				],
			]
		);
	}

	public function cmsFields() {
		return [
			new DropdownField(self::MediaLinkTypeFieldName, 'Link type', [
				EmbedCode::EmbedCodeOption       => $this->translatedMessage(EmbedCode::EmbedCodeOption, 'Label', 'Embed Code'),
				Media::UploadedFileOption        => $this->translatedMessage(Media::UploadedFileOption, 'Label', 'Uploaded File'),
				ExternalLink::ExternalLinkOption => $this->translatedMessage(ExternalLink::ExternalLinkFieldName, 'Label', 'External Link'),
			]),
		];
	}

	/**
	 * Show/hide External and EnbedCode fields depending on selected MediaLinkType.
	 *
	 * @param \FormField $field
	 * @param array      $allFieldConstraints
	 */
	public function customFieldConstraints(FormField $field, array $allFieldConstraints) {
		if (ClassInfo::exists('DisplayLogicCriteria')) {
			$fieldName = $field->getName();

			if ($fieldName == ExternalLink::ExternalLinkFieldName) {
				// hide external link field unless MediaLinkType field is that option
				$field->hideUnless(self::MediaLinkTypeFieldName)->isEqualTo(ExternalLink::ExternalLinkOption);

			} elseif ($fieldName == EmbedCode::EmbedCodeFieldName) {
				// hide embed code link field unless MediaLinkType field is that option
				$field->hideUnless(self::MediaLinkTypeFieldName)->isEqualTo(EmbedCode::EmbedCodeOption);

			} elseif ($fieldName == Media::UploadFieldName) {
				// hide upload field unless MediaLinkType field is that option
				$field->hideUnless(self::MediaLinkTypeFieldName)->isEqualTo(Media::UploadedFileOption);
			}
		}
	}
}
