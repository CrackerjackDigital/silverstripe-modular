<?php
namespace Modular\Fields;

use OptionsetField;

class DisplayLocation extends Field {
	const DisplayLocationFieldName = 'DisplayLocation';
	const DisplayInContent         = 'InContent';
	const DisplayInSidebar         = 'InSidebar';
	const DisplayInBoth            = 'InBoth';

	private static $enum_values = [
		self::DisplayInContent,
		self::DisplayInSidebar,
		self::DisplayInBoth,
	];
	/**
	 * Return static db enum schema definition for the DisplayABC constants.
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
					self::DisplayLocationFieldName => 'enum("' . $values . '")'
				],
			]
		);
	}

	public function cmsFields() {
		return [
			new OptionsetField(self::DisplayLocationFieldName, 'Shows in', [
				self::DisplayInContent => $this->translatedMessage(self::DisplayLocationFieldName, self::DisplayInContent . ".Label", 'Page content'),
				self::DisplayInSidebar => $this->translatedMessage(self::DisplayLocationFieldName, self::DisplayInSidebar . ".Label", 'Side bar'),
				self::DisplayInBoth    => $this->translatedMessage(self::DisplayLocationFieldName, self::DisplayInBoth . ".Label", 'Page content and Sidebar'),
			]),
		];
	}

}