<?php
namespace Modular\Fields;

use Director;
use DisplayLogicWrapper;
use DropdownField;
use FieldList;
use FormField;
use TextField;
use TreeDropdownField;

/**
 * Add fields and functionality to LinkAttribute model.
 */
class LinkAttributeField extends \Modular\Field {
	const ExternalLinkFieldName    = 'ExternalLink';
	const InternalLinkRelationship = 'InternalLink';
	const ExternalLinkValue        = self::ExternalLinkFieldName;
	const InternalLinkValue        = self::InternalLinkRelationship;
	const LinkTypeFieldName        = 'LinkType';

	private static $db = [
		self::LinkTypeFieldName     => "enum('ExternalLink,InternalLink')",
		self::ExternalLinkFieldName => 'Text',
	];
	private static $has_one = [
		self::InternalLinkRelationship => 'SiteTree',
	];
	private static $summary_fields = [
		\Modular\Fields\Title::TitleFieldName => 'Title',
		self::LinkTypeFieldName             => 'Link Type',
		'ResolvedLink'                      => 'Link', // method value
	];

	public function updateCMSFields(FieldList $fields) {
		$fields->removeByName(self::InternalLinkRelationship . 'ID');
		parent::updateCMSFields($fields);
	}

	public function cmsFields($mode) {
		$types = [
			self::ExternalLinkValue => $this->fieldDecoration('LinkType', 'ExternalLink', 'External Link'),
			self::InternalLinkValue => $this->fieldDecoration('LinkType', 'InternalLink', 'Internal Link'),
		];

		return [
			new DropdownField(self::LinkTypeFieldName, '', $types),
			new TextField(self::ExternalLinkFieldName),
			(new DisplayLogicWrapper(
				new TreeDropdownField(self::InternalLinkRelationship . 'ID', '', 'SiteTree')
			))->setName(self::InternalLinkRelationship),
		];
	}

	/**
	 * Hook in display logic to show/hide fields depending on field type selected
	 *
	 * @param \FormField $field
	 */
	public function customFieldConstraints(FormField $field, array $allFieldConstraints) {
		if ($fieldName = $field->getName()) {
			// this is the field name of the DisplayLogicWrapper, not the field itself.
			if ($fieldName == self::InternalLinkRelationship) {
				$field->hideUnless(self::LinkTypeFieldName)->isEqualTo(self::InternalLinkValue);
			}
			if ($fieldName == self::ExternalLinkFieldName) {
				$field->hideUnless(self::LinkTypeFieldName)->isEqualTo(self::ExternalLinkValue);
			}
		}
	}

	/**
	 * Usefull for templates to indicate external links
	 *
	 * @return bool
	 */
	public function IsExternal() {
		return $this()->LinkType == self::ExternalLinkValue;
	}

	/**
	 * Usefull for templates to indicate indicate links
	 *
	 * @return bool
	 */
	public function IsInternal() {
		return !$this->IsExternal();
	}

	/**
	 * Returns text of link, either as entered for External or generated from Internal. If Internal an target page
	 * isn't found then returns LinkAttributeExtension.InternalLink.MissingTarget message e.g. '[linked page not found]' type message
	 *
	 * @return string
	 */
	public function ResolvedLink() {
		if ($this->IsExternal()) {
			$externalLink = $this()->ExternalLink;
			if (!Director::is_absolute_url($externalLink)) {
				return 'http://' . $externalLink;
			} else {
				return $externalLink;
			}
		}

		return $this()->InternalLink()
			? $this()->InternalLink()->Link()
			: $this->fieldDecoration(
				'InternalLink',
				'MissingTarget',
				'[linked page not found or not set]'
			);
	}

}
