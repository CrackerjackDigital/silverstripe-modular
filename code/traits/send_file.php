<?php
namespace Modular\Traits;

use Controller;
use diversen\sendfile;
use Exception;
use Page_Controller;

trait send_file {

	/**
	 * Send file courtesy of library https://packagist.org/packages/diversen/http-send-file
	 *
	 * @param string $fileName will be converted to absolute path if not already absolute
	 * @param string $downloadName name of file when it is downloaded
	 *
	 * @return bool|\SS_HTTPResponse
	 * @throws \diversen\Exception
	 */
	protected function sendFile( $fileName, $downloadName ) {
		try {
			if (substr($fileName, 0, strlen(BASE_PATH)) !== BASE_PATH) {
				$fileName = Controller::join_links(\Director::baseFolder(), $fileName);
			}
			static::send_file($fileName, $downloadName);

		} catch ( Exception $e ) {
			Page_Controller::set_session_message( "Sorry, an error has occurred preparing your archive, please try again later" );
		}

		return $this->redirectBack();
	}

	protected static function send_file($fileName, $downloadName) {
		$sendFile = new sendfile();
		$sendFile->contentDisposition( $downloadName );
		$sendFile->send( $fileName, true );
	}
}