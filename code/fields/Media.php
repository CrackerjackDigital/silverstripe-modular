<?php
namespace Modular\Fields;

abstract class Media extends File {
	const MediaLinkOption = 'Media';
	const UploadFieldName = 'MediaID';

	private static $base_upload_folder = 'media';

	private static $allowed_files = 'audio,video';

}