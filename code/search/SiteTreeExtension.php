<?php
namespace Modular\Search;

class SiteTreeExtension extends ModelExtension {
	private static $fulltext_fields = [
		'Title'   => 'PartialMatchFilter',
		'Content' => 'PartialMatchFilter',
	];
}