<?php
namespace Modular\Helpers;

use Modular\Object;

class Reflection extends Object {
	use \Modular\Traits\reflection;
	
	// bit field bits for various relationship types, for HasOne the arity is 1, for others arity != 1 so
	// can also be used to determine the arity of the relationship as one or more than one.
	const HasOne          = 1;
	const HasMany         = 2;
	const ManyMany        = 4;
	const BelongsManyMany = 8;
	
	const AllRelationships = 15;
	
	// map a numeric arity to a config variable name
	private static $arity_config_map = [
		self::HasOne          => 'has_one',
		self::HasMany         => 'has_many',
		self::ManyMany        => 'many_many',
		self::BelongsManyMany => 'belongs_many_many',
	];
	
}