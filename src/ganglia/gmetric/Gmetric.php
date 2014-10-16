<?php
namespace Ganglia\Gmetric;

//use GmetricMessage;

class Gmetric 
{

	public static function sendMetric($metric, $type, $value, $unit, $group = null, $sampleRate = null)
	{
		// TODO: Check if the $sampleRate param is provided, and if so, suppress the metric selectively.

		// Instantiate a Gmetric message using the input parameters.
		$message = new GmetricMessage($metric, $type, $value, $unit, $group);
		$message->send();
		$message = null;
	}

	// TODO: Consider implementing a counter feature that stores incoming data in a static variable.
	// This is useful for repeated actions in a single PHP page view. It is not viable in scenarios that require thread safety.
	// The calling class can ask to send the summed data explicitly, or it will be sent at teardown (if not cleared).

	public static function main($confFile = null)
	{
		self::sendMetric('testapp.testmetric', GmetricMessage.UINT16, 2100, "count", "app");
	}
	
}
