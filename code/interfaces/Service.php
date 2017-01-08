<?php
namespace Modular\Interfaces;

use Member;

interface Service {
	/**
	 * Make a request of a service, which generally calls request by extensions on Request extensions added to the service.
	 * Service request extensions should only do something with the request if the serviceName requested matches their own
	 * serviceName (which is generally their class name).
	 *
	 * @param string      $serviceName generally the name of the class which should respond to the request
	 * @param mixed       $data        a model, some other format useful to the service
	 * @param null        $options     options for request, e.g. to queue the data, process immediately etc
	 * @param Member|null $requester   who requested the service, or null if current logged in member
	 * @return mixed
	 */
	public function request($serviceName, $data, $options = null, $requester = null);
}