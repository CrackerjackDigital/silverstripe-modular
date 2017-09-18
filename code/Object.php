<?php
namespace Modular;

require_once( __DIR__ . '/traits/bitfield.php' );
require_once( __DIR__ . '/traits/cache.php' );
require_once( __DIR__ . '/traits/config.php' );
require_once( __DIR__ . '/traits/lang.php' );
require_once( __DIR__ . '/traits/reflection.php' );

use Modular\Traits\bitfield;
use Modular\Traits\cache;
use Modular\Traits\config;
use Modular\Traits\debugging;
use Modular\Traits\lang;
use Modular\Traits\reflection;

class Object extends \Object {
	use bitfield;
	use cache;
	use config;
	use debugging;
	use lang;
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
