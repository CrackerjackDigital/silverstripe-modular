<?php
namespace Modular\Relationships;
/**
 * Add a single related page field.
 *
 * @package Modular\Relationships
 */
class HasRelatedPage extends HasOne {
	const RelationshipName = 'Page';
	const RelatedClassName = 'Page';
}