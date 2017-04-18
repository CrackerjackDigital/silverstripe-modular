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

	// extensions, e.g. Fields, will add to this, can also be configured via normal config mechanisms
	private static $validation = [];

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
	 * Get all validation rules from Fields extending this Model which should have updated the model via config mechanism.
	 * @return array
	 */
	public function validationRules() {
		return $this->config()->get('validation');
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
