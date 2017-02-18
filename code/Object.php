<?php
namespace Modular;

use Modular\Traits\bitfield;
use Modular\Traits\cache;
use Modular\Traits\config;
use Modular\Traits\debugging;
use Modular\Traits\lang;
use Modular\Traits\reflection;

require_once 'traits/bitfield.php';
require_once 'traits/cache.php';
require_once 'traits/config.php';
require_once 'traits/debugging.php';
require_once 'traits/emailer.php';
require_once 'traits/enabler.php';
require_once 'traits/json.php';
require_once 'traits/lang.php';
require_once 'traits/options.php';
require_once 'traits/owned.php';
require_once 'traits/reflection.php';
require_once 'traits/related.php';
require_once 'traits/requirements.php';
require_once 'traits/tokens.php';
require_once 'traits/upload.php';

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
