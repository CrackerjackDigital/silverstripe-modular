<?php
namespace Modular\Blocks;

use Modular\Interfaces\LinkType;
use Modular\Model;

/**
 * Class which represents a block which can be added to an Article, of types ( in display order ). The types in the grid dropdown are determined by
 * subclasses of this class, so there is no need e.g. for a 'BlockType' lookup or relationship.
 * 'Text',
 * 'Video',
 * 'Audio',
 * 'Images (gallery)',
 * 'Image (full width)',
 * 'Footnotes',
 * 'Links',
 * 'Download',
 * 'Pull Quote'
 */
class Block extends \Modular\VersionedModel implements LinkType {
	private static $template = '';

	private static $summary_fields = [
		'BlockType' => 'Block Type',
	    'BlockZones' => 'Zone(s)'
	];

	private static $link_type = '';

	private $customFilterTags = [];

	public function addCustomFilterTag($tag) {
		$this->customFilterTags[ $tag ] = $tag;
	}

	public function customFilterTags() {
		return $this->customFilterTags;
	}

	public function BlockType() {
		return $this->i18n_singular_name();
	}

	/**
	 * Return a csv of zones for this block class.
	 * @return mixed
	 */
	public function BlockZones() {
		$zones = [];
		$blocksForZone = \Config::inst()->get('Page', 'blocks_for_zone');

		foreach ($blocksForZone as $zone => $zoneBlocks) {
			foreach ($zoneBlocks as $blockClass) {
				if ($blockClass == $this->ClassName) {
					$zones[] = $zone;
				}
			}
		}
		return implode(', ', $zones);
	}

	/**
	 * Returns:
	 *  -   configured config.link_type for this block
	 *
	 * or if not configured
	 *
	 *  -   terminal part of the class name of this block without namespace and without 'Block' suffix
	 *
	 * # VideoBlock => 'Video'
	 * # Modular\Blocks\CallToAction => 'CallToAction'
	 *
	 * @return string
	 */
	public function LinkType() {
		if (!$linkType = $this->config()->get('link_type')) {
			$linkType = current(array_reverse(explode('\\', static::block_class())));
			$linkType = (substr($linkType, -5) == 'Block')
				? substr($linkType, 0, -5)
				: $linkType;
		}
		return $linkType;
	}

	/**
	 * Return text to show in a link to this block (or more likely a link this block contains, such as a File via the HasLinks interface).
	 * @return mixed
	 */
	public function LinkText() {
		$blockClass = get_class($this);
		return _t("$blockClass.LinkText", 'MORE');
	}

	/**
	 * @return string
	 */
	public static function block_class() {
		return get_called_class();
	}

	public function DisplayInSidebar() {
		return false;
	}

	public function DisplayInContent() {
		return true;
	}

	/**
	 * Ok so this makes Blocks a 'Model-View' but we already have that via ViewableData so run with it.
	 *
	 * @return \HTMLText
	 */
	public function forTemplate() {
		return $this->renderWith($this->templates());
	}

	protected function template() {
		return $this->config()->get('template') ?: $this->class;
	}

	protected function templates() {
		return [$this->template()];
	}

	/**
	 * Return the current page from Director.
	 *
	 * @return \Page
	 */
	public function CurrentPage() {
		/** @var \Page $parent */
		return \Director::get_current_page();
	}

	/**
	 * Return current pages ClassName.
	 * @return string
	 */
	public function PageClassName() {
		return \Director::get_current_page()->ClassName;
	}

}
