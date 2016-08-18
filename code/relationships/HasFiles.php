<?php
namespace Modular\Relationships;

use SS_List;

/**
 * @method SS_List Links
 */
class HasFiles extends ManyMany {
	const RelationshipName = 'Files';
	const RelatedClassName = 'Modular\Models\File';



}