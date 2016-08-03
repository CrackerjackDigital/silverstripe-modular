<?php

interface ImageryInterface {
	/**
	 * Return a list of images (potentially only one) or an empty list.
	 * @return SS_List
	 */
	public function Images();

	/**
	 * Return a single image (potentially the first of a list), or null
	 * @return Image|null
	 */
	public function Image();
}