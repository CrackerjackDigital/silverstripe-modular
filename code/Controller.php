<?php
namespace Modular;

class Controller extends \Controller {
	use debugging;

	public function __invoke() {
		return $this;
	}
}