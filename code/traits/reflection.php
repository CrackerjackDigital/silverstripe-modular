<?php

namespace Modular\Traits;

trait reflection {
	/**
	 * Should return a Model (DataObject)
	 *
	 * @return \DataObject
	 */
	abstract public function __invoke();

	/**
	 * Remove namespace from a class and trim supplied from start or end of class name
	 * e.g. Modular\Edges\SocialOrganisation -> Organisation
	 *
	 * @param       $className
	 * @param bool  $stripNamespace remove the namespace if present
	 * @param array $trim this from the start or end of the class name
	 *
	 * @return string
	 */
	public static function name_from_class_name( $className, $stripNamespace = true, $trim = [ 'Model', 'Social' ] ) {
		$className = $stripNamespace ? static::strip_namespace( $className ) : $className;

		foreach ( $trim as $prefixOrSuffix ) {
			$len = strlen( $prefixOrSuffix );

			if ( substr( $className, 0, $len ) == $prefixOrSuffix ) {
				$className = substr( $className, $len );
			}
			if ( substr( $className, - $len ) == $prefixOrSuffix ) {
				$className = substr( $className, 0, - $len );
			}
		}

		return $className;
	}

	/**
	 * Return the class name of the passed thing
	 *
	 * @param Object|mixed $modelOrClassName
	 * @param bool         $stripNamespace
	 *
	 * @return string
	 */
	public static function derive_class_name( $modelOrClassName, $stripNamespace = false ) {
		if ( $modelOrClassName ) {
			$modelOrClassName = is_object( $modelOrClassName ) ? get_class( $modelOrClassName ) : $modelOrClassName;
			if ( $stripNamespace && is_array( $modelOrClassName ) ) {
				foreach ( $modelOrClassName as &$className ) {
					$className = static::strip_namespace( $className );
				}
			} else if ( $stripNamespace ) {
				$modelOrClassName = static::strip_namespace( $modelOrClassName );
			}
		}

		return $modelOrClassName;
	}

	/**
	 * Return a list of all extensions on the extended model which implement (or are a
	 * class or subclass of) the provided interfaceName via instanceof.
	 *
	 * @param string $interfaceName
	 *
	 * @return array of [ className => extensionInstance ]
	 */
	public function extensionsByInterface( $interfaceName ) {
		$extensions = [];
		foreach ( $this()->getExtensionInstances() as $extension ) {
			if ( $extension instanceof $interfaceName ) {
				$extensions[ get_class( $extension ) ] = $extension;
			}
		}

		return $extensions;
	}

	/**
	 * Return an map of subclasses of the called class.
	 *
	 * TODO: implement 'depthFirst' flag to get the 'leaf' classes of heirarchy first
	 *
	 * @param bool $excludeThisClass if true then don't include the called class in the list
	 *
	 * @return array with [ 'namespaced class name' => 'no-namespaced class name' ] for each subclass
	 */
	public static function subclasses( $excludeThisClass = true ) {
		$classes = [];

		foreach ( \ClassInfo::subclassesFor( get_called_class() ) as $className ) {
			if ( $excludeThisClass && ( $className == get_called_class() ) ) {
				continue;
			}
			$classes[ $className ] = $className;
		}

		return $classes;
	}

	/**
	 * Return class name without namespace if there is one passed.
	 *
	 * @param string $maybeNamespacedClassName
	 *
	 * @return string
	 */
	public static function strip_namespace( $maybeNamespacedClassName = null ) {
		return current( array_reverse( explode( '\\', $maybeNamespacedClassName ?: get_called_class() ) ) );
	}
}