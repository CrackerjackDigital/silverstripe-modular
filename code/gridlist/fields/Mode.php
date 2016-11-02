<?php
namespace Modular\GridList\Fields;

use Modular\Fields\Field;
use Modular\GridList\Interfaces\TempleDataProvider;

/**
 * Add a field which allows the mode for the gridlist to be selected, if the mode is provided elsewhere then that will be the value instead and
 * it can not be changed.
 *
 * @package Modular\GridList\Fields
 */
class Mode extends Field implements TempleDataProvider {
	const SingleFieldName = 'GridListMode';
	const SingleFieldSchema = 'enum("Grid,List","Grid")';

	/**
	 * If GridList also has another way to provide mode, then set the field to that mode if it is set and don't
	 * let it change.
	 */
	public function cmsFields() {
		$fields = parent::cmsFields();
		// has the mode been provided some other way?
		$data = [];
		$source = get_class($this);

		if ($otherWays = array_filter($this()->extend('provideGridListTemplateData', $data, $source))) {
			foreach ($otherWays as $otherWay) {
				if (isset($otherWay['Mode'])) {
					// replace the field with a read-only field set to the first other mode found
					$fields[ static::SingleFieldName ] = new \ReadonlyField(static::SingleFieldName, null, $otherWay['Mode']);
				}
			}
		}
		return $fields;
	}

	/**
	 * @param array $existingData already set by other extensions, not altered but new values to add/override returned instead.
	 * @return array [ 'Mode' => mode from dropdown or '' ] mode is lowercase
	 */
	public function provideGridListTemplateData($existingData = []) {
		if ($this()->{static::SingleFieldName}) {
			return [
				'Mode' => strtolower($this()->{static::SingleFieldName})
			];
		} else {
			return [
				'Mode' => isset($existingData['Mode']) ? $existingData['Mode'] : 'grid'
			];
		}
	}
}