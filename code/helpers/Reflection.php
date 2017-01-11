<?php
namespace Modular\Helpers;

use Modular\Interfaces\Arities;
use Modular\Object;

class Reflection extends Object implements Arities  {

	const AllRelationships = 15;

	// map a numeric arity to a config variable name
	private static $arity_config_map = [
		self::HasOne          => 'has_one',
		self::HasMany         => 'has_many',
		self::ManyMany        => 'many_many',
		self::BelongsManyMany => 'belongs_many_many',
	];

}