<?php

namespace Modular\Fields;

class HashToken extends Field {
	const SingleFieldName = 'HashToken';
	const SingleFieldSchema = 'Varchar(128)';
	
	private static $max_length = 128;
	
	/**
	 * If HashToken is not set on the model then generate a new one.
	 */
	public function onBeforeWrite()
	{
		parent::onBeforeWrite();
		if (!$this->singleFieldValue()) {
			$this->singleFieldValue($this->generate());
		}
	}
	
	public function generate() {
		return substr(0, static::max_length(), md5(uniqid('', true)));
	}
	
	public static function max_length() {
		return static::config()->get('max_length');
	}
}