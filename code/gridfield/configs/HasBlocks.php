<?php
namespace Modular\GridField;

/**
 * Alters the config to be suitable for adding/removing blocks from an article.
 *
 * Adds an 'AddNewMultiClass' selector
 */
class HasBlocksGridFieldConfig extends HasManyManyGridFieldConfig {
	private static $add_new_multi_class = true;
}