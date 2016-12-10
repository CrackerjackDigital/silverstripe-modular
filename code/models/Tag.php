<?php
namespace Modular\Models;

use Modular\Fields\ModelTag;
use Modular\VersionedModel;

class Tag extends VersionedModel {
	// convenience
	const TagFieldName = ModelTag::SingleFieldName;
}