<?php
namespace Modular\Relationships;
use Modular\Fields\ManyManyTagField;

/**
 * Adds a multiple free text Tags relationship TagField to Tag model to extended model.
 *
 * @package Modular\Fields
 */

class HasTags extends ManyManyTagField {
	const RelationshipName = 'Tags';
	const RelatedClassName = 'Modular\Models\Tag';

}