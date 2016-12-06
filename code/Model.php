<?php
namespace Modular;

class Model extends \DataObject {
	use lang;
	use related;
	use reflection;
	use debugging;

	/**
	 * Invoking a type returns itself.
	 *
	 * @return $this
	 */
	public function __invoke() {
		return $this;
	}

	/**
	 * Patch until php 5.6 static::class is widely available on servers
	 *
	 * @return string
	 */
	public static function class_name() {
		return get_called_class();
	}

}
