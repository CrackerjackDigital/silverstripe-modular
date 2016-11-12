<?php
namespace Modular;

class VersionedModel extends \DataObject {
	use lang;
	use related;

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