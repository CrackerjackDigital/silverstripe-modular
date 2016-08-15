<?php
namespace Modular\Models;

use \Modular\Model;

class Slide extends Model {
	private static $has_one = [
		'CarouselBlock' => 'Modular\Blocks\Block'
	];
}