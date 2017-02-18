<?php
namespace Modular\Interfaces;

interface HasMode {
	const DefaultMode = '';
	
	public function mode($setMode = self::DefaultMode);
}