<?php
namespace Modular\Relationships;

use SS_List;

/**
 * @method SS_List Links
 */
class HasLinks extends HasManyMany {
	const RelationshipName = 'Links';
	const RelatedClassName = 'Modular\Models\InternalOrExternalLink';
	const GridFieldConfigName = 'Modular\GridField\HasLinksGridFieldConfig';


}