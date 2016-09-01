<?php
namespace Modular\GridField;

/**
 * Alters the config to be suitable for adding/removing blocks from an article.
 *
 * Adds an 'AddNewMultiClass' selector
 */
class HasBlocksGridFieldConfig extends HasManyManyGridFieldConfig {
	private static $add_new_multi_class = true;

	// only allow blocks brought through to the application
	// TODO shouldn't have to enumerate and so amend when new classes added, perhaps fnmatch is not best exclusion algorythm?
	private static $exclude_related_classes = [
		'Modular*',
		'GridListContentBlock',
		'GridListDownloadBlock',
		'GridListImageBlock',
		'GridListLinkedPageBlock',
		'GridListSubscribeBlock',
		'GridListVideoBlock',
	];

	public static function allowed_related_classes() {
		$out = [];
		foreach (\ClassInfo::subclassesFor('Modular\Blocks\Block') as $className) {
			$out[$className] = singleton($className)->i18n_singular_name();
		}
		return $out;
	}

}