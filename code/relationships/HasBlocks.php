<?php
namespace Modular\Relationships;

use Modular\Blocks\Block;
use Modular\GridField\GridFieldConfig;
use Versioned;

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

	// if this is set as a list of class names then will override the Configs list of allowed classes
	// in this case the related class names are Block class names
	private static $allowed_related_classes = [];

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
			->sort(\Modular\Fields\Relationship::GridFieldOrderableRowsFieldName, 'ASC');

		$this()->extend('postRenderZoneBlocks', $zone, $rules, $blocks);
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

	public function onAfterUnpublish() {
		parent::onAfterPublish();

		/** @var Block|\Versioned $block */
		/** @var \SS_List $linkedPages */

		if ($blocks = $this->related()) {
			foreach ($blocks as $block) {
				if ($block->hasExtension('Versioned')) {
					if (!$this->hasLinks($block)) {
						$oldMode = Versioned::get_reading_mode();
						Versioned::reading_stage('Live');
						$block->delete();
						Versioned::set_reading_mode($oldMode);
					}
				}
			}
		}
	}

	/**
	 * Checks if a block has links to a page other than the current page.
	 * @param $block
	 * @return bool
	 */
	protected function hasLinks($block) {
		$linkedPages = $block->Pages()->exclude('ID', $this()->ID);
		return $linkedPages->count();
	}

	/**
	 * Sets the data model class on a HasBlocks gridfield to be 'Modular\Blocks\Block' as 'GridListBlock' is set
	 * otherwise, and that is not the root for these blocks.
	 *
	 * @param null $relationshipName
	 * @param null $configClassName
	 * @return \GridField
	 */
	protected function gridField($relationshipName = null, $configClassName = null) {
		if ($gridField = parent::gridField($relationshipName, $configClassName)) {
			$gridField->setModelClass('Modular\Blocks\Block');
			$gridField->setList($this()->{static::relationship_name()}());
		}
		return $gridField;
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