<?php
namespace Modular\Relationships;
use Modular\Blocks\Block;

/**
 * Add a gridfield to which blocks can be added and managed.
 *
 * @method \DataList Blocks
 */
class HasBlocks extends HasManyMany {
	const RelationshipName    = 'Blocks';
	const RelatedClassName    = 'Modular\Blocks\Block';
	const GridFieldConfigName = 'Modular\GridField\HasBlocksGridFieldConfig';

	const RulesExcludePrefix = '!';
	const RulesDelimeter     = ',';

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
	 * Blocks can be added in addition to the zone by specifying block class names in the rules parameter (delimited by RulesDelimiter),
	 * or excluded by adding them prefixed with a '!' (RulesExcludePrefix).
	 *
	 * @param string $zone
	 * @param string $rules delimited block class names e.g. '!ExcludeThisBlockClass, AddThisBlockClass'
	 * @return \DataList
	 */
	public function ZoneBlocks($zone = 'Content', $rules = '') {
		$this()->extend('preRenderZoneBlocks', $zone, $rules);

		$map = $this()->config()->get('blocks_for_zone')
			?: $this->config()->get('blocks_for_zone');

		$includes = [];

		if (isset($map[ $zone ])) {
			$includes = $map[ $zone ];
		}

		list($excludes, $additionals) = $this->parseRules($rules);

		// add additional blocks from rules and make sure they are unique
		$includes = array_unique(
			array_merge(
				$includes,
				$additionals
			)
		);

		$blocks = $this()
			->Blocks()
			->filter('ClassName', $includes)
			->exclude('ClassName', $excludes)
			->sort(\Modular\GridField\GridField::GridFieldOrderableRowsFieldName);

		$this()->extend('postRenderZoneBlocks', $zone, $rules);
		return $blocks;
	}

	/**
	 * Deep publish owned blocks when the owner is published.
	 */
	public function onAfterPublish() {
		if ($blocks = $this->related()) {
			/** @var Block|\Versioned $block */
			foreach ($blocks as $block) {
				if ($block->hasExtension('Versioned')) {
					$block->publish('Stage', 'Live');
					// now ask the block to publish it's own blocks.
					$block->extend('onAfterPublish');
				}
			}
		}
	}

	/**
	 * Parse a string of rules such as '!NotBlockClass, AddBlockClass' int array of includes, excludes for filtering
	 *
	 * @param string|array $rules
	 * @return array tuple of exclude, include arrays of class names e.g. [ [ 'NotBlockClass' ], [ 'AddBlockClass'] ]
	 */
	protected function parseRules($rules) {
		$rules = is_array($rules) ? $rules : array_filter(explode(static::RulesDelimeter, $rules));

		$excludes = [];
		$includes = [];

		foreach ($rules as $rule) {
			if (substr($rule, 0, 1) == static::RulesExcludePrefix) {
				$excludes[] = substr($rule, 1);
			} else {
				$includes[] = $rule;
			}
		}
		return [
			$excludes,
		    $includes,
		];
	}
}