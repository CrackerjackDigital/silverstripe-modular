<?php
namespace Modular;

class Model extends \DataObject {
	use lang;

	/**
	 * Invoking a model returns itself.
	 * @return $this
	 */
	public function __invoke() {
		return $this;
	}
}
