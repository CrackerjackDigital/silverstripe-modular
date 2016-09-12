<?php
namespace Modular;

class Model extends \DataObject {
	use lang;

	/**
	 * Invoking a model returns itself.
	 * @return $this
	 */
	public function __invoke() {
		return $this;
	}

	public static function class_name() {
		return get_called_class();
	}
}
