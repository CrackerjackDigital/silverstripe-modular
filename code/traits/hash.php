<?php

namespace Modular\Traits;

trait hash {

	/**
	 * Return a model.
	 *
	 * @return \DataObject
	 */
	abstract public function __invoke();

	/**
	 * Calculate and return a new hash, e.g. using md5 trait.
	 *
	 * @param        $value
	 * @param mixed  $salt
	 * @param string $method
	 * @param bool   $raw
	 *
	 * @return mixed
	 */
	abstract public function hash( $value = '', $salt = '', &$method = 'md5', $raw = false );

	/**
	 * If field on model is empty then fill with a new hash.
	 *
	 * Returns the value of the field if it was rehashed otherwise null
	 *
	 * @param        $fieldName
	 * @param        $value
	 * @param        $salt
	 * @param string $method
	 *
	 * @return null|string
	 */
	protected function hashFieldIfEmpty( $fieldName, $value, $salt, &$method = ''  ) {
		if ( empty( $this()->{$fieldName} ) ) {
			return $this->hashField( $fieldName, $value, $salt, $method );
		}

		return null;
	}

	/**
	 * Calculate and set a new hash on the field from the salt.
	 *
	 * @param        $fieldName
	 * @param        $value
	 * @param        $salt
	 * @param string $method
	 *
	 * @return mixed
	 */
	protected function hashField( $fieldName, $value, $salt, &$method = '' ) {
		return $this()->{$fieldName} = $this->hash( $value, $salt, $method );
	}
}