<?php
namespace Modular\Interfaces;

interface HasChildren {
	/**
	 * @return \SS_List
	 */
	public function Children();
}