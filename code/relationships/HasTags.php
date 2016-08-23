<?php
namespace Modular\Relationships;
use Modular\Fields\HasManyManyTagField;

/**
 * Adds a multiple free text Tags relationship TagField to Tag model to extended model.
 *
 * @package Modular\Fields
 */

class HasTags extends HasManyManyTagField {
	const RelationshipName = 'Tags';
	const RelatedClassName = 'Modular\Models\Tag';

}