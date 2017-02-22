<?php
namespace Modular;

use Modular\Traits\debugging;
use Modular\Traits\lang;
use Modular\Traits\reflection;
use Modular\Traits\related;

/**
 * Model
 *
 * @package Modular
 * @property int ID
 */
class Model extends \DataObject {
	use lang;
	use related;
	use reflection;
	use debugging;

	/**
	 * Invoking a type returns itself.
	 *
	 * @return $this
	 */
	public function __invoke() {
		return $this;
	}

	public function model() {
		return $this();
	}

	/**
	 * Returns the model's class with '_' instead of namespace seperator.
	 * @return string
	 */
	public static function type() {
		return str_replace('\\', '_', static::class_name());
	}

	/**
	 * Patch until php 5.6 static::class is widely available on servers
	 *
	 * @return string
	 */
	public static function class_name() {
		return get_called_class();
	}

	public function modelClassName() {
		return get_class($this);
	}

	public function modelID() {
		return $this->ID;
	}
}
