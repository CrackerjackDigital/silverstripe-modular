<?php
namespace Modular\Relationships;

use SS_List;
use Modular\Model;

/**
 * @method SS_List Links
 */
class Links extends ManyMany {
	const RelationshipName = 'Links';
	const RelatedClassName = 'Modular\Models\InternalOrExternalLink';

}