<?php
namespace Modular\Search;

class FileExtension extends ModelExtension {
	private static $searchable_fields = [
		'Title'   => 'PartialMatchFilter',
	];

}