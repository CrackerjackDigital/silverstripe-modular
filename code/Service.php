<?php
namespace Modular;

abstract class Service extends Object implements \Modular\Interfaces\Service {
	// service name to use with Injector when creating an instance, if not set then the called class name will be used
	const ServiceName = '';

	// override the ServiceName with a custom name, will be used in preference to self.ServiceName
	private static $service_name = '';

	/**
	 * Service interface method.
	 *
	 * @param null   $params
	 *
	 * @param string $resultMessage
	 *
	 * @return mixed
	 */
	abstract public function execute( $params = null, &$resultMessage = '' );

	/**
	 * Return a configured instance of the service via config.service_name, self.ServiceName or the called class name.
	 *
	 * @param mixed  $options
	 * @param string $env
	 *
	 * @return \Modular\Interfaces\Service
	 */
	public static function get( $options = null, $env = '' ) {
		$serviceName = static::config()->get( 'service_name' )
			?: static::ServiceName
				?: get_called_class();

		return \Injector::inst()->create( $serviceName, $options, $env );
	}

}