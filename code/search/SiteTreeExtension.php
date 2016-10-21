<?php
namespace Modular\Search;

class SiteTreeExtension extends ModelExtension {
	private static $searchable_fields = [
		'Title'   => 'PartialMatchFilter',
		'Content' => 'PartialMatchFilter',
	];
}