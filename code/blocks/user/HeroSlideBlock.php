<?php
namespace Modular\Blocks;

class HeroSlideBlock extends Block {
	private static $db = [
		"Title" => "Varchar(255)",
		"Content" => "HTMLText",
		"BackgroundColorScheme" => "Enum('topics,environ,theme,sand,sage','topics')",
		"SortOrder" => "Int",
	];

	private static $has_one = [
		"Image" => "Image",
	];

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->removeByName('SortOrder');

		return $fields;
	}

	public static $default_sort = 'SortOrder';

	public static $singular_name = "Hero Slide";
	public static $plural_name = "Hero Slides";

}