<?php
namespace Modular\GridField;

/**
 * Alters the config to be suitable for adding/removing blocks from an article.
 *
 * Adds an 'AddNewMultiClass' selector
 */
class HasBlocksGridFieldConfig extends HasManyManyGridFieldConfig {
	private static $add_new_multi_class = true;

	private static $prefix_zones = true;

	private static $prune_unzoned_blocks = true;

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

	/**
	 * For the add blocks dropdown we can optionally prefix the label in the dropdown with the zone from Block.BlockZones.
	 * If config.prune_unzoned_blocks is true then we also remove blocks which currently do not have a zone.
	 *
	 * @param array $addExtraClasses
	 * @return array
	 */
	public static function limited_related_classes($addExtraClasses = []) {
		$pruned = [];

		$classes = parent::limited_related_classes($addExtraClasses);
		if (static::config()->get('prefix_zones')) {

			// reference to $label so we update the label in place incase we are returning all blocks, not just those with zones
			foreach ($classes as $class => &$label) {
				if (\ClassInfo::exists($class)) {

					$single = singleton($class);
					if ($single->hasMethod('BlockZones')) {

						if ($zones = $single->BlockZones()) {
							$label = "$zones - $label";
							$pruned[$class] = $label;
						}
					}
				}
			}
		}
		return static::config()->get('prune_unzoned_blocks') ? $pruned : $classes;
	}

}