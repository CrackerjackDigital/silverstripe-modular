<?php

namespace Modular\Helpers;

use Modular\Debugger;
use Modular\Object;
use Modular\Traits\tokens;

class Strings extends Object {
	use tokens;

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
