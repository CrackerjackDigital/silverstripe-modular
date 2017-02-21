<?php
namespace Modular;

class ScopedReference {
	private $reference;

	/**
	 * Save a reference to the object and the callable to call when this ScopedReference falls out of scope..
	 *
	 * @param Object   $object to track during lifetime of this instance of ScopedReference
	 * @param callable $onDestruct (takes single parameter which will be the object being referenced).
	 */
	public function __construct($object, callable $onDestruct) {
		$this->reference = $object;
		$this->destruct = $onDestruct;
	}

	/**
	 * Calls the destruct callable passed in constructor passing the object as first parameter
	 */
	public function __destruct() {
		$destruct = $this->destruct;
		$destruct($this->reference);
	}
}