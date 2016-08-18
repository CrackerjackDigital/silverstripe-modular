<?php
namespace Modular\Relationships;

class HasSlides extends HasMany {
	const RelationshipName    = 'Slides';
	const RelatedClassName    = 'Modular\Models\Slide';
	const GridFieldConfigName = 'Modular\GridField\GridFieldConfig';
}