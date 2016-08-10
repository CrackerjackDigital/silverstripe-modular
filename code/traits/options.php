<?php
namespace Modular;

trait options {
	private $options = [];

	/**
	 * Get, set or update options.
	 *
	 * @param int|array|null $options
	 * @param bool           $merge     if false then what is passed overwrites existing
	 *                                  if true then will be merged in (if an array)
	 *                                  or logical ored (if an int)
	 *                                  or set otherwise
	 * @return null
	 */
	public function options($options = [], $merge = true) {
		if (func_num_args()) {
			if (!$merge) {
				$this->options = $options;
			} elseif (is_array($options)) {
				$this->options = array_merge_recursive($this->options, $options);
			} elseif (is_int($options)) {
				$this->options |= $options;
			} else {
				$this->options = $options;
			}
		}
		return $this->options;
	}
}

