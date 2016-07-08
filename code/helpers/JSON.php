<?php
namespace Modular\Helpers;

use Modular\ModularObject;

class JSON extends ModularObject {
	// JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE
	private static $json_encode_options = 288;

	private static $json_decode_options = 0;

	private static $objects_as_arrays = true;

	private static $decode_depth = 512;

	public static function json_encode_options() {
		return static::config()->get('json_encode_options');
	}

	public static function json_decode_options() {
		return static::config()->get('json_decode_options');
	}

	/**
	 * Json encode the data
	 *
	 * @param       $data
	 * @param array $optionsOverride override default options
	 * @return string
	 */
	public static function encode($data, $optionsOverride = []) {
		return json_encode($data, $optionsOverride || static::json_encode_options());
	}

	/**
	 * Encode to a json string suitable for emitting as a template variable.
	 *
	 * @param $data
	 * @return mixed
	 * @internal param null $returnArrayForObject
	 */
	public static function template_encode($data) {
		$out = [];

		foreach ($data as $key => $value) {
			if (is_array($value)) {
				$out[] = $key . ': {' . self::template_encode($value) . '}';
			} elseif ($value) {
				$out[] = $key . ": '" . $value . "'";
			}
		}
		$s = implode(", ", $out);
		return $s;
	}

	public static function decode($data, $returnArrayForObject = null) {
		$returnArrayForObject = is_null($returnArrayForObject)
			? static::config()->get('objects_as_arrays')
			: $returnArrayForObject;

		$decodeDepth = static::config()->get('decode_depth');

		return json_decode($data, $returnArrayForObject, $decodeDepth, static::json_decode_options());
	}
}