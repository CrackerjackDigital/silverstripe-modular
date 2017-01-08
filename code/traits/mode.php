<?php
namespace Modular\Traits;

use Modular\Interfaces\HasMode;

trait mode {
	/** @var string $mode */
	protected $mode = HasMode::DefaultMode;
	
	/**
	 * @param mixed $setMode new mode if provided
	 * @return string|$this
	 * @fluid-getter-setter
	 */
	public function mode($setMode = null) {
		if (func_num_args()) {
			$this->mode = $setMode;
			return $this;
		} else {
			return $this->mode;
		}
	}
}