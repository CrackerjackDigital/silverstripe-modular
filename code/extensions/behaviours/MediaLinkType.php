<?php
namespace Modular\Behaviours;

use ClassInfo;
use DropdownField;
use FormField;
use Modular\Fields\EmbedCode;
use Modular\Fields\ExternalLink;
use Modular\Fields\Field;
use Modular\Fields\Media;

/**
 * Link type field and logic for a model which has an EmbedCode, Media and ExternalLink fields.
 */
class MediaLinkType extends Field {
	const MediaLinkTypeFieldName = 'MediaLinkType';

	private static $enum_values = [
		\Modular\Fields\EmbedCode::EmbedCodeOption,
		\Modular\Fields\Media::MediaLinkOption,
		\Modular\Fields\ExternalLink::ExternalLinkOption,
	];

	/**
	 * Return static db enum schema definition for the Media and ExternalLink Option constants.
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
				EmbedCode::EmbedCodeOption       => $this->fieldDecoration(EmbedCode::EmbedCodeOption, 'Label', 'Embed Code'),
				Media::MediaLinkOption => $this->fieldDecoration(Media::MediaLinkOption, 'Label', 'Uploaded Media'),
				ExternalLink::ExternalLinkOption => $this->fieldDecoration(ExternalLink::ExternalLinkOption, 'Label', 'External Link'),
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

			} elseif ($fieldName == Media::field_name()) {
				// hide upload field unless MediaLinkType field is that option
				$field->hideUnless(self::MediaLinkTypeFieldName)->isEqualTo(Media::MediaLinkOption);
			}
		}
	}

	public function IsExternalLink() {
		return $this()->{self::MediaLinkTypeFieldName} == ExternalLink::ExternalLinkOption;
	}

	public function IsMedia() {
		return $this()->{self::MediaLinkTypeFieldName} == Media::MediaLinkOption;
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
		} elseif ($this->IsMedia()) {
			return $this()->{Media::field_name()}()->Link();
		} elseif ($this->IsEmbedCode()) {
			return $this()->{EmbedCode::EmbedCodeFieldName};
		}
	}

}
