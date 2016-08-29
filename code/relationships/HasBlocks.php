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

	// add block class names for each 'zone' in templates here, then include in template with
	// ZoneBlocks('Top')
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
	 * REturns Blocks for a particular zone on the page, e.g. 'Content', 'Sidebar' filtered
	 * by Block class name from config.blocks_for_zone.
	 *
	 * @param string $zone
	 * @return \DataList
	 */
	public function ZoneBlocks($zone = 'Content') {
		$map = $this->config()->get('blocks_for_zone');

		$filters = [];

		if (isset($map[ $zone ])) {
			$filters = [
				'ClassName' => $map[ $zone ],
			];
		}
		return $this()->Blocks()->filter($filters)->sort(\Modular\GridField\GridField::GridFieldOrderableRowsFieldName);
	}}