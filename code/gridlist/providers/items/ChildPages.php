<?php
namespace Modular\GridList\Providers\Items;

use Modular\Fields\Field;
use Modular\GridList\Interfaces\ItemsProvider;
use Modular\ModelExtension;

class ChildPages extends Field implements ItemsProvider {
	const SingleFieldName = 'ProvideChildren';
	const SingleFieldSchema = 'Boolean';

	private static $defaults = [
		self::SingleFieldName => true
	];

	public function provideGridListItems() {
		if ($this()->{static::SingleFieldName}) {
			return $this()->Children();
		}
	}

}