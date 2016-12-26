<?php
namespace Modular;

class Controller extends \Controller {
	/**
	 * Invoking a controller returns the controller itself.
	 * @return $this
	 */
	public function __invoke() {
		return $this;
	}
}