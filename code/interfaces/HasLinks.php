<?php
namespace Modular\Interfaces;
/**
 * Interface HasLinks for models which have one or more links, either internal or external.
 *
 * @package Modular\Interfaces
 */
interface HasLinks {
	/**
	 * Returns a list of links, if model has a single link then that will be first element of the list.
	 *
	 * Links are returned as ArrayData objects with:
	 * # Link = text of link to the model, page file or external link as text
	 * # Title = the title of the link if it can be found
	 * # LinkType = e.g. 'File', 'Image', 'Video', 'Audio', 'ExternalLink', 'Page' etc returned via implementation of the LinkType interface
	 *
	 * @return \ArrayList
	 */
	public function LinkInfo();
}