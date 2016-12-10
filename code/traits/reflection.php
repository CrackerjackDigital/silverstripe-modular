<?php
namespace Modular;

trait reflection {
	/**
	 * Remove namespace from a class and trim supplied from start or end of class name
	 * e.g. Modular\Edges\SocialOrganisation -> Organisation
	 *
	 * @param $className
	 * @return string
	 */
	public static function name_from_class_name($className, $trim = ['Model', 'Social']) {
		$className = static::strip_namespace($className);
		foreach ($trim as $prefixOrSuffix) {
			$len = strlen($prefixOrSuffix);

			if (substr($className, 0, $len) == $prefixOrSuffix) {
				$className = substr($className, $len);
			}
			if (substr($className, -$len) == $prefixOrSuffix) {
				$className = substr($className, 0, -$len);
			}
		}
		return $className;
	}

	/**
	 * Return an map of subclasses of the called class.
	 *
	 * TODO: implement 'depthFirst' flag to get the 'leaf' classes of heirarchy first
	 *
	 * @param bool $excludeThisClass if true then don't include the called class in the list
	 * @return array with [ 'namespaced class name' => 'no-namespaced class name' ] for each subclass
	 */
	public static function subclasses($excludeThisClass = true) {
		$classes = [];

		foreach (\ClassInfo::subclassesFor(get_called_class()) as $className) {
			if ($excludeThisClass && ($className == get_called_class())) {
				continue;
			}
			$classes[$className] = static::strip_namespace($className);
		}
		return $classes;
	}

	/**
	 * Return class name without namespace if there is one passed.
	 * @param string $maybeNamespacedClassName
	 * @return string
	 */
	public static function strip_namespace($maybeNamespacedClassName) {
		return current(array_reverse(explode('\\', $maybeNamespacedClassName)));
	}
}