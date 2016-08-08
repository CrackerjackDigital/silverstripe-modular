<?php
namespace Modular\Behaviours;

use Modular\Fields\Field;
use Modular\Fields\InternalLink;
use Modular\Fields\ExternalLink;
use ClassInfo;
use DropdownField;
use FormField;

class InternalOrExternalLink extends Field {
	const LinkTypeFieldName = 'LinkType';

	private static $enum_values = [
		\Modular\Fields\InternalLink::InternalLinkOption,
		\Modular\Fields\ExternalLink::ExternalLinkOption,
	];

	public function IsExternalLink() {
		return $this()->{self::LinkTypeFieldName} == ExternalLink::ExternalLinkOption;
	}

	public function IsInternalLink() {
		return $this()->{self::LinkTypeFieldName} == InternalLink::InternalLinkOption;
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
					self::LinkTypeFieldName => 'enum("' . $values . '")'
				],
			]
		);
	}

	public function cmsFields() {
		return [
			new DropdownField(self::LinkTypeFieldName, 'Link type', [
				InternalLink::InternalLinkOption => $this->translatedMessage(InternalLink::InternalLinkFieldName, 'Label', 'Internal link'),
				ExternalLink::ExternalLinkOption => $this->translatedMessage(ExternalLink::ExternalLinkFieldName, 'Label', 'External link'),
			])
		];
	}

	/**
	 * Show/hide fields using display_logic depending on the LinkType field added by this extension.
	 *
	 * @param \FormField $field
	 * @param array      $allFieldConstraints
	 */
	public function customFieldConstraints(FormField $field, array $allFieldConstraints) {
		if (ClassInfo::exists('DisplayLogicCriteria')) {
			$fieldName = $field->getName();

			if ($fieldName == InternalLink::InternalLinkFieldName) {
				$field->hideUnless(self::LinkTypeFieldName)->isEqualTo(InternalLink::InternalLinkOption);
			} elseif ($fieldName == ExternalLink::ExternalLinkFieldName) {
				$field->hideUnless(self::LinkTypeFieldName)->isEqualTo(ExternalLink::ExternalLinkOption);
			}
		}
	}
}