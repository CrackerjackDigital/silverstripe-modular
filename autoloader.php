<?php
// if we are flushing then autoload traits from code/traits directory.
if (isset($_REQUEST['flush'])) {
	if (!file_exists(__DIR__ . '/_manifest_exclude')) {
		spl_autoload_register(function ($class) {
			$class = current(array_reverse(explode('\\', $class)));
			if (strtolower($class) == $class) {
				if (file_exists($path = __DIR__ . "/code/traits/$class.php")) {
					require_once($path);
				}
				return;
			}
		});
	}
}
