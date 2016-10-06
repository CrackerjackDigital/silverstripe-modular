<?php
namespace Modular\Interfaces;
/**
 * Declare an interface for exceptions for RTTI and type-hinting, inspection reports etc
 *
 * @package Modular\Interfaces
 */
interface Exception {
	public function getMessage();

	public function getCode();

	public function getTrace();

	public function getFile();

	public function getLine();

	public function getPrevious();
}