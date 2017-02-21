<?php
namespace Modular\Interfaces;

use Modular\Types\Type;

interface Field extends Type {
	public function cmsFields($mode = null);
}
