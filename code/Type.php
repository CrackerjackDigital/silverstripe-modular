<?php
namespace Modular;

class Type extends \DataObject {
	use lang;
	use related;

	private static $indexes = [
		'Code' => true
	];
	/**
	 * Invoking a model returns itself.
	 *
	 * @return $this
	 */
	public function __invoke() {
		return $this;
	}

	public static function class_name() {
		return get_called_class();
	}
}
