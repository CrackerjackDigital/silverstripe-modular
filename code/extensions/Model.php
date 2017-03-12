<?php
namespace Modular;

use DataExtension;
use Modular\Traits\config;
use Modular\Traits\debugging;
use Modular\Traits\enabler;
use Modular\Traits\owned;
use Modular\Traits\related;

class ModelExtension extends DataExtension {
	use config;
	use enabler;
	use owned;
	use debugging;
	use related;

	/**
	 * Return the extended model.
	 * @return \DataObject|\Modular\Model
	 */
	public function model() {
		return $this();
	}

	/**
	 * Workaround for PHP which doesn't do static::class
	 * @return string
	 */
	public static function class_name() {
		return get_called_class();
	}

	/**
	 * Writes the extended model and returns it if write returns truthish, otherwise returns null.
	 *
	 * @return \Modular\Model|null
	 * @throws \ValidationException
	 */
	public function writeAndReturn() {
		if ($this()->write()) {
			return $this();
		}
		return null;
	}

	/**
	 * Remove db, has_one etc fields from the field list which are defined in the extension, e.g. they may be replaced with a widget.
	 *
	 * @param \FieldList $fields
	 * @param bool      $removeDBFields
	 * @param bool      $removeHasOneFields
	 */
	protected static function remove_own_fields(\FieldList $fields, $removeDBFields = true, $removeHasOneFields = true) {
		$ownDBFields = $removeDBFields
			? (\Config::inst()->get(get_called_class(), 'db') ?: [])
			: [];
		$ownHasOneFields = $removeHasOneFields
			? (\Config::inst()->get(get_called_class(), 'has_one') ?: [])
			: [];

		$ownFields = array_merge(
			array_keys($ownDBFields),
			array_map(
				function ($item) {
					return $item . 'ID';
				},
				array_keys($ownHasOneFields)
			)
		);

		array_map(
			function ($fieldName) use ($fields) {
				$fields->removeByName($fieldName);
			},
			$ownFields
		);
	}

}
