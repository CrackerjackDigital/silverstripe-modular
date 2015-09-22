<?php

trait lang {
	function lang($key, $default = '', array $tokens = []) {
		return _t(get_called_class(), ".$key", $default ?: $key, $tokens);
	}
}