<?php
namespace Modular\Interfaces;

interface Arities {
	// bit field bits for various relationship types, for HasOne the arity is 1, for others arity != 1 so
	// can also be used to determine the arity of the relationship as one or more than one.
	const HasOne          = 1;
	const HasMany         = 2;
	const ManyMany        = 4;
	const BelongsManyMany = 8;

	const AllArities = 15;
}