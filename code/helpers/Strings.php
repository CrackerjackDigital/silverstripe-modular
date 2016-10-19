<?php

namespace Modular\Helpers;

use Modular\Debugger;
use Modular\Object;

class Strings extends Object {

	/**
	 * Replace {token} in string from provided map of token => value
	 *
	 * @param       $string
	 * @param array $replaceWith
	 * @return mixed
	 */
	public static function detokenise($string, array $replaceWith) {
		$tokens = array_map(
			function ($token) {
				return '{' . $token . '}';
			},
			array_keys(
				$replaceWith
			)
		);

		return str_replace(
			$tokens,
			array_values($replaceWith),
			$string
		);
	}

	/**
	 * Given a CamelCASEString returns a 'Proper CASE String' preserving acronyms
	 *
	 * @param string $in
	 * @param string $join the output string with this, set to '' to get a CamelCASEString again.
	 * @return string
	 */
	public static function decamel($in, $join = ' ') {
		return implode($join, preg_split("/((?<=[a-z])(?=[A-Z])|(?=[A-Z][a-z]))/", $in));
	}

	/**
	 * Given a snake-case-string returns a 'Proper Case String'
	 *
	 * @param string $in
	 * @param string $join the output string parts with this, set to '' for a CamelCaseString
	 * @return string
	 */
	public static function desnake($in, $join = ' ') {
		return implode($join, array_map('ucfirst', explode(' ', str_replace(['_', '-'], ' ', $in))));
	}

	/**
	 * Given a possibly namespaced class name return it as decamelised terminal class name.
	 *
	 * e.g. '\ShimpleDinging\Relationships\UberShimpleDinger' would become 'Uber Shimple Dinger'
	 *
	 * @param $className
	 * @return string
	 */
	public static function class_to_label($className) {
		return self::decamel(current(array_reverse(explode('\\', $className))));
	}

}
