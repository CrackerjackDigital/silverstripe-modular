<?php
namespace Modular\Search;

class FileExtension extends ModelExtension {
	private static $fulltext_fields = [
		'Title'   => 'PartialMatchFilter',
	];

}