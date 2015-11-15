<?php
/**
 * Calls through to JSON class while traits are worked out properly in SS.
 */
namespace Modular;

trait json {
	public function encode($data) {
		return Helpers\JSON::encode($data);
	}
	public function decode($string) {
		return Helpers\JSON::decode($string);
	}

	/**
	 * Encode suitable for a javascript template variable.
	 * @param $data
	 */
	public function template_encode($data) {
		return Helpers\JSON::template_encode($data);
	}
}