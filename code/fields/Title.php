<?php
namespace Modular\Fields;

use TextField;

class Title extends Field {
	const SingleFieldName = 'Title';
	const SingleFieldSchema = 'Varchar(255)';

	// convenience
	const TitleFieldName = self::SingleFieldName;

}