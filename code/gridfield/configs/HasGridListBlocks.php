<?php
namespace Modular\GridField;

/**
 * Alters the config to be suitable for adding/removing manually managed (curated) blocks on a gridfield.
 *
 */
class HasGridListBlocksGridFieldConfig extends HasManyManyGridFieldConfig {
	private static $add_new_multi_class = true;

	private static $exclude_related_classes = [
		'Modular*'
	];
}