<?php
namespace Modular\GridList\Fields;

use Modular\Fields\Field;
use Modular\GridList\Interfaces\GridListTempleDataProvider;

class Mode extends Field implements GridListTempleDataProvider {
	const SingleFieldName = 'GridListMode';
	const SingleFieldSchema = 'enum("Grid,List","Grid")';

	public function provideGridListTemplateData($existingData = []) {
		return [
			'Mode' => strtolower($this()->{static::SingleFieldName})
	    ];
	}
}