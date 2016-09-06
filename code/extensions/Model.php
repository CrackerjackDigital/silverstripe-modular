<?php
namespace Modular;

use Modular\config;
use Modular\enabler;
use Modular\owned;
use \DataExtension;

class ModelExtension extends DataExtension {
	use config;
	use enabler;
	use owned;

	public static function class_name() {
		return get_called_class();
	}
	/**
	 * Writes the extended model and returns it if write returns truthish, otherwise returns null.
	 *
	 * @return Model|null
	 */
	public function writeAndReturn() {
		if ($this()->write()) {
			return $this();
		}
		return null;
	}

}
