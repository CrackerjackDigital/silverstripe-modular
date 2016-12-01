<?php
namespace Modular;

use Modular\Fields\Code;

class Type extends \DataObject {
	/**
	 * Invoking a type returns itself.
	 * @return $this
	 */
	public function __invoke() {
		return $this;
	}

	/**
	 * Patch until php 5.6 static::class is widely available on servers
	 * @return string
	 */
	public static function class_name() {
		return get_called_class();
	}

	public function onBeforeWrite() {
		parent::onBeforeWrite();
		$this->ClassName = get_class($this);
	}

	public static function get_by_code($code) {
		return static::get()->filter(Code::SingleFieldName, $code)->first();
	}
}