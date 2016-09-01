<?php
namespace Modular\Relationships;

/**
 * Add a gridfield to which blocks can be added and managed.
 *
 * @method \DataList Blocks
 */
class HasBlocks extends HasManyMany {
	const RelationshipName    = 'Blocks';
	const RelatedClassName    = 'Modular\Blocks\Block';
	const GridFieldConfigName = 'Modular\GridField\HasBlocksGridFieldConfig';

	private static $cms_tab_name = 'Root.ContentBlocks';

	private static $allow_new_multi_class = true;

	// add block class names for each 'zone' in templates here, then include in template with
	// ZoneBlocks('Top'). Can be consfigured on extended class (e.g. Page) which will take precedence over
	// those declared in here the extension.
	private static $blocks_for_zone = [
		# example:
		#   'HomePageTop' => [
		#       'HomePageFeatureBlock'
		#   ],
		#	'Top' => [
		#		'HeroBlock'
		#	 ],
		#    'Content' => [
		#	    'ContentBlock',
		#       'PromoBlock'
		#    ],
		#    'SideBar' => [
		#       'PromoBlock'
		#    ],
		#    'Bottom' => [
		#	    'FootnotesBlock'
		#    ]
	];

	/**
	 * Returns Blocks for a particular zone on the page, e.g. 'Content', 'Sidebar' filtered
	 * by Block class name from config.blocks_for_zone on the extended model first, then on this extension if not
	 * no config.blocks_for_zone at all is set on there (does not allow mixing of blocks from the model and this extension
	 * at the moment).
	 *
	 * @param string $zone
	 * @return \DataList
	 */
	public function ZoneBlocks($zone = 'Content') {
		$map = $this()->config()->get('blocks_for_zone')
			?: $this->config()->get('blocks_for_zone');

		$filters = [];

		if (isset($map[ $zone ])) {
			$filters = [
				'ClassName' => $map[ $zone ],
			];
		}
		return $this()->Blocks()->filter($filters)->sort(\Modular\GridField\GridField::GridFieldOrderableRowsFieldName);
	}
}