<?php
namespace Modular\Fields;
/**
 * Field represents a media file (e.g. Audio, Video etc) with a relationship called 'Media'
 *
 * @package Modular\Fields
 */
class Media extends File {
	const RelationshipName = 'Media';
	const MediaLinkOption = 'Media';

	private static $base_upload_folder = 'media';

	private static $allowed_files = 'audio,video';

}