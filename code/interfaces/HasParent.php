<?php
namespace Modular\Interfaces;

interface HasParent {
	/**
	 * @return \Modular\Model|\DataObject
	 */
	public function Parent();
}