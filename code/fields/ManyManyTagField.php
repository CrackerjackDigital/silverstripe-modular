<?php
namespace Modular\Fields;
use Modular\Relationships\HasManyMany;

/**
 * Adds a tag field representation of a HasManyMany relationship
 *
 * @package Modular\Fields
 */

class HasManyManyTagField extends HasManyMany {
	private static $allow_multiple = true;
	private static $allow_create = true;

	// force tags field view
	private static $show_as = self::ShowAsTagsField;

}