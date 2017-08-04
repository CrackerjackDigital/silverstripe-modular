<?php
namespace Modular;

use Modular\Interfaces\Service as ServiceInterface;

abstract class Service extends Object implements ServiceInterface {
	// service name to use with Injector when creating an instance, if not set then the called class name will be used
	const ServiceName = '';

	// override the ServiceName with a custom name, will be used in preference to self.ServiceName
	private static $service_name = '';

	/**
	 * Fixure out service name from config, const or called class.
	 * @return string
	 */
	public static function service_name() {
		return static::config()->get( 'service_name' )
			?: static::ServiceName
				?: get_called_class();
	}
	/**
	 * Return a configured instance of the service via config.service_name, self.ServiceName or the called class name.
	 *
	 * @param mixed  $options
	 * @param string $env
	 *
	 * @return \Modular\Interfaces\Service
	 */
	public static function create( $options = null, $env = '' ) {
		$serviceName = static::service_name();

		return \Injector::inst()->create( $serviceName, $options, $env);
	}

	/**
	 * Return a configured instance of the service via config.service_name, self.ServiceName or the called class name.
	 *
	 * @param mixed  $options
	 * @param string $env
	 *
	 * @return \Modular\Interfaces\Service
	 */
	public static function get( $options = null, $env = '' ) {
		$serviceName = static::service_name();

		return \Injector::inst()->get( $serviceName, true, [ $options, $env ] );
	}

}