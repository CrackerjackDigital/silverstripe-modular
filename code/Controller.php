<?php
namespace Modular;

use Modular\Interfaces\HasMode;
use Modular\Traits\debugging;
use Modular\Traits\mode;

class Controller extends \Controller implements HasMode {
	use mode;
	use debugging;
	/**
	 * Invoking a controller returns the controller itself.
	 * @return $this
	 */
	public function __invoke() {
		return $this;
	}
}