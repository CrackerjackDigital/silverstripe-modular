<?php

namespace Modular\Blocks;

use HiddenField;
use Modular\Application;
use Modular\Interfaces\LinkType;

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
 *
 * @method \DataList Pages()
 */
class Block extends \Modular\VersionedModel implements LinkType {
	use \Modular\Traits\duplication;

	private static $template = '';

	private static $summary_fields = [
		'BlockType'  => 'Block Type',
		'BlockZones' => 'Zone(s)',
	];

	private static $link_type = '';

	// for each field as key this will be prefixed to the value on duplication
	private static $prefix_duplicated_fields = [
		#	'Title' => 'Copy of ',
	];

	// set in modular/_config/config.yml
	private static $skip_duplicate_related_classes = [
	];

	/**
	 * When we do an add new multi class we need to tell it what the ClassName is.
	 *
	 * @return \FieldList
	 */
	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->push( new HiddenField( 'ClassName', '', get_class( $this ) ) );

		return $fields;
	}

	public function BlockType() {
		return $this->i18n_singular_name();
	}

	/**
	 * Return a csv of zones for this block class.
	 *
	 * @return mixed
	 */
	public function BlockZones() {
		$zones         = [];
		$blocksForZone = \Config::inst()->get( 'Page', 'blocks_for_zone' );

		foreach ( $blocksForZone as $zone => $zoneBlocks ) {
			foreach ( $zoneBlocks as $blockClass ) {
				if ( $blockClass == $this->ClassName ) {
					$zones[] = $zone;
				}
			}
		}

		return implode( ', ', $zones );
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
		if ( ! $linkType = $this->config()->get( 'link_type' ) ) {
			$linkType = current( array_reverse( explode( '\\', static::block_class() ) ) );
			$linkType = ( substr( $linkType, - 5 ) == 'Block' )
				? substr( $linkType, 0, - 5 )
				: $linkType;
		}

		return $linkType;
	}

	/**
	 * Return text to show in a link to this block (or more likely a link this block contains, such as a File via the HasLinks interface).
	 *
	 * @return mixed
	 */
	public function LinkText() {
		$blockClass = get_class( $this );

		return _t( "$blockClass.LinkText", 'MORE' );
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
		return $this->renderWith( $this->templates() );
	}

	protected function template() {
		return $this->config()->get( 'template' ) ?: $this->class;
	}

	protected function templates() {
		return [ $this->template() ];
	}

	/**
	 * Return the current page from Director.
	 *
	 * @return \Page
	 */
	public function CurrentPage() {
		/** @var \Page $parent */
		return Application::get_current_page();
	}

	/**
	 * Return current pages ClassName.
	 *
	 * @return string
	 */
	public function PageClassName() {
		return Application::get_current_page()->ClassName;
	}

}
