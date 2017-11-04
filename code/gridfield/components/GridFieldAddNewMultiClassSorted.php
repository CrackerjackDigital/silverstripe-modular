<?php
namespace Modular\GridField\Components;

use Modular\Traits\config;
use Modular\Traits\owner;

/**
 * A component which lets the user select from a list of classes to create a new record form.
 *
 * By default the list of classes that are createable is the grid field's model class, and any
 * subclasses. This can be customised using {@link setClasses()}.
 */
class GridFieldAddNewMultiClassSorted extends GridFieldAddNewMultiClass {
	use owner;
	use config;
}