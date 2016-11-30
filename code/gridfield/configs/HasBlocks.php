<?php
namespace Modular\GridField\Configs;

/**
 * Alters the config to be suitable for adding/removing blocks from an article.
 *
 * Adds an 'AddNewMultiClass' selector
 */
class HasBlocks extends HasManyManyGridFieldConfig {
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
	    'HeroSlideBlock'                // home page hero slider only on home page.
	];

	public static function allowed_related_classes() {
		$out = [];
		foreach (\ClassInfo::subclassesFor('Modular\Block') as $className) {
			$out[ $className ] = singleton($className)->i18n_singular_name();
		}
		return $out;
	}

	/**
	 * For the add blocks dropdown we can optionally prefix the label in the dropdown with the zone from Block.BlockZones.
	 * If config.prune_unzoned_blocks is true then we also remove blocks which currently do not have a zone.
	 * Blocks are prefixed with zones from Page blocks_for_zone and ordered in the dropdown as the zones in that array are ordered.
	 *
	 * @param array $addExtraClasses
	 * @return array
	 */
	public static function limited_related_classes($addExtraClasses = []) {
		$pruned = [];

		$allowedClasses = parent::limited_related_classes($addExtraClasses);
		asort($allowedClasses);

		if (static::config()->get('prefix_zones')) {
			$blocksForZone = singleton('Page')->config()->get('blocks_for_zone');
			foreach ($blocksForZone as $zone => $blockClasses) {
				asort($blockClasses);

				foreach ($allowedClasses as $class => &$label) {
					foreach ($blockClasses as $blockClass) {
						if ($blockClass == $class) {
							$label = "$zone - $label";
							$pruned[ $class ] = $label;

							// we only allow a block to show in one zone at the moment
							// TODO fix if we want blocks to appear e.g. in Content and SideBar Zones
							break;
						}
					}
				}
			}
		}
		return static::config()->get('prune_unzoned_blocks') ? $pruned : $allowedClasses;
	}

}