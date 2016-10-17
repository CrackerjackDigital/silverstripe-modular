<?php
namespace Modular;

trait reflection {
	/**
	 * Return an map of subclasses of the called class.
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