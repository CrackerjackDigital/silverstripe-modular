<?php
namespace Modular\Interfaces;
/**
 * Interface LinkType implemented by models which can be linked to with different link types, such as a 'Download', 'Page' or 'Video'
 *
 * @package Modular\Interfaces
 */
interface LinkType {
	/**
	 * Returns a general link type such as 'Download', 'Page' or 'Video' in mixed case, can be casified in css.
	 * @return string
	 */
	public function LinkType();

	/**
	 * Return the text to show in a link to this model.
	 * @return string
	 */
	public function LinkText();
}