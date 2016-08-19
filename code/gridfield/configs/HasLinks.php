<?php
namespace Modular\GridField;
use Modular\Relationships\ManyMany;

/**
 * Alters the config to be suitable for adding/removing links from a block
 */
class HasLinksGridFieldConfig extends GridFieldConfig {
	private static $add_new_multi_class = false;
}