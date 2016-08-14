<?php
namespace Modular\Relationships;

use Modular\Fields\Field;

/**
 * RelatedPages
 *
 * @package Modular\Relationships
 * @method RelatedPages
 */
class RelatedPages extends Field {
	const RelatedClassName = '';
	const RelationshipName = '';

	private static $multiple_select = true;

	private static $cms_tab_name = 'Root.Relationships';

	public function extraStatics($class = null, $extension = null) {
		$parent = parent::extraStatics($class, $extension) ?: [];

		return array_merge_recursive(
			$parent,
			[
				'many_many' => [
					static::RelationshipName => static::RelatedClassName
				]
			]
		);
	}

	public function cmsFields() {
		$multipleSelect = (bool) $this->config()->get('multiple_select');

		return [
			(new \TagField(
				static::RelationshipName,
				null,
				$this->otherPages()
			))->setIsMultiple($multipleSelect)
		];
	}
	/**
	 * @return \SS_List
	 */
	public function otherPages() {
		$relatedClassName = static::RelatedClassName;
		return $relatedClassName::get();
	}

}