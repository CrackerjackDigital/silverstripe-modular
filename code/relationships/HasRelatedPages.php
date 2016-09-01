<?php
namespace Modular\Relationships;

/**
 * RelatedPages
 *
 * @package Modular\Relationships
 * @method RelatedPages
 */
abstract class HasRelatedPages extends HasManyMany {
	private static $multiple_select = true;

	private static $cms_tab_name = 'Root.RelatedPages';

	private static $sortable = false;

}