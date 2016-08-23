<?php
namespace Modular\Fields;

use Modular\Relationships\HasManyMany;
use SS_List;

/**
 * @method SS_List Links
 */
class Links extends HasManyMany {
	const RelationshipName = 'Links';
	const RelatedClassName = 'Modular\Models\Link';

}