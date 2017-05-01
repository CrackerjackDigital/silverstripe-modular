<?php
namespace Modular;

use Modular\Traits\bitfield;
use Modular\Traits\cache;
use Modular\Traits\config;
use Modular\Traits\debugging;
use Modular\Traits\lang;
use Modular\Traits\reflection;

class Object extends \Object {
	use cache;
	use lang;
	use bitfield;
	use config;
	use debugging;
	use reflection;

	/**
	 * Invoking an Object returns itself.
	 * @return $this
	 */
	public function __invoke() {
		return $this;
	}
	public static function class_name() {
		return get_called_class();
	}

}
