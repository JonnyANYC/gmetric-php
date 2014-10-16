<?php
namespace Ganglia\Gmetric;

class GmetricMessage 
{
	private $metric;
	private $type;
	private $value;
	private $unit;
	private $group;

	public function __construct($metric, $type, $value, $unit, $group = null)
	{		
		// Store the inputs. Throw an exception if we don't have enough info to form a valid Gmetric message.
	}
	
	public function send()
	{
		$this->sendViaFileHandle($message);
	}

	private static function sendViaFileHandle($message)
	{
		// Open the UDP socket to send the data.
	
		// Send the header.
		// Send the payload.
	
		// Close the socket.
	
		// dereference the handles.
	}
	
	private static function sendViaSocket($message)
	{
		throw new Exception("Not implemented yet.");
	}
	

	/**
	 * Generate the header on-demand, based on the stored inputs. 
	 */
	private function getHeader()
	{
		packIntAsXdr();
	}
	
	/**
	 * Generate the payload on-demand, based on the stored inputs. 
	 */
	private function getPayload()
	{
		packStringAsXdr();
	}
	
	private function packIntAsXdr($int)
	{
		$this->header .= "NYI";
	}
	
	private function packStringAsXdr($string)
	{
		$this->header .= "NYI";
	}
	
}
