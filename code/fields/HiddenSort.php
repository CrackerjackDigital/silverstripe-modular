<?php
namespace Modular\Fields;

class HiddenSort extends Field {
	const SingleFieldName = 'Sort';
	const SingleFieldSchema = 'Int';
	const ReadOnly = true;

	/**
	 * In CMS replace the field with a Read Only field.
	 * @return array
	 */
	public function cmsFields() {
		if (static::ReadOnly) {
			$fields = parent::cmsFields();
			$fields[ static::SingleFieldName ] = new \HiddenField(static::SingleFieldName);
			return $fields;
		}
	}
}