<?php
namespace Modular;

class Model extends \DataObject {
	/**
	 * Invoking a model returns itself.
	 * @return $this
	 */
	public function __invoke() {
		return $this;
	}
}