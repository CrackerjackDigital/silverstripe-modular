<?php
namespace Modular\Interfaces;

use Modular\Types\TypeInterface;

interface Field extends TypeInterface {
	public function cmsFields($mode = null);
}
