<?php
namespace Modular\Fields;

use Modular\Relationships\ManyMany;
use SS_List;

/**
 * @method SS_List Links
 */
class Links extends ManyMany {
	const RelationshipName = 'Links';
	const RelatedClassName = 'Modular\Models\Link';

}