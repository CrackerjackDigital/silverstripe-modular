<?php
namespace Modular;

use DataObject;
use Quaff\Exceptions\Mapping;
use Quaff\Mappers\ArrayMapper;

require_once 'traits/bitfield.php';
require_once 'traits/cache.php';
require_once 'traits/config.php';
require_once 'traits/debugging.php';
require_once 'traits/enabler.php';
require_once 'traits/json.php';
require_once 'traits/lang.php';
require_once 'traits/owned.php';
require_once 'traits/options.php';
require_once 'traits/tokens.php';

class Object extends \Object {
	use cache;
	use lang;
	use bitfield;
	use config;
	use debugging;

	/**
	 * Invoking an Object returns itself.
	 * @return $this
	 */
	public function __invoke() {
		return $this;
	}

}