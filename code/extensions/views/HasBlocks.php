<?php
namespace Modular\Extensions\Views;

use Modular\owned;

class HasBlocks extends \SiteTreeExtension {
	use owned;
	
	private static $blocks_for_zone = [
		'Hero' => 'HeroSlideBlock',
		'Top' => ['CTATopBlock', 'HeroBlock'],
		'Content' => [
			'AudioBlock',
			'ContentBlock',
			'ContentImageLeftBlock',
			'ContentImageRightBlock',
			'DownloadBlock',
			'FullWidthImageBlock',
			'GridListBlock',
			'ImageGalleryBlock',
			'LinksBlock',
			'MenuGridBlock',
			'FactsBlock',
			'VideoBlock',
			'CarouselBlock',
		],
		'Bottom' => ['CTABottomBlock'],
		'Footer' => 'FootNotesBlock',
	];

	/**
	 * Returns Blocks for a particular zone on the page, e.g. 'Content', 'Sidebar' filtered
	 * by Block class name from config.blocks_for_zone.
	 *
	 * @param string $zone
	 * @return \DataList
	 */
	public function ZoneBlocks($zone = 'Content') {
		$map = $this()->config()->get('blocks_for_zone');

		$filters = [];

		if (isset($map[$zone])) {
			$filters = [
				'ClassName' => $map[$zone],
			];
		}
		return $this()->Blocks()->filter($filters);
	}
}