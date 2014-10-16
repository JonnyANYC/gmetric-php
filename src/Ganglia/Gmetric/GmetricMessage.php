<?php
namespace JonnyANYC\Ganglia\Gmetric;

class GmetricMessage 
{
	private $name;
	private $type;
	private $value;
	private $unit;
	private $group;

	public function __construct($name, $type, $value, $unit, $group = null)
	{
		// Store the inputs. Throw an exception if we don't have correct info to form a valid Gmetric message.
		$this->name = $name;
		$this->type = $type;
		$this->value = $value;
		$this->unit = $unit;
		$this->group = $group;
	}
	
	public function send()
	{
		$this->sendViaFileHandle();
	}

	private function sendViaFileHandle()
	{
		// Open the UDP socket to send the data.
	
		// Send the header.
		// DEBUG
		echo $this->getHeader();
	
		// Send the payload.
		// DEBUG
		echo $this->getPayload();
		
		// Close the socket.
		
		// dereference the handles.
	}
	
	private function sendViaSocket()
	{
		throw new Exception("Not implemented yet.");
	}
	

	/**
	 * Generate the header on-demand, based on the stored inputs. 
	 */
	private function getHeader()
	{
		$header = $this->packStringAsXdr($this->name); 
		return $header;
		// DEBUG incomplete
		
	}

	/**
	 * Generate the payload on-demand, based on the stored inputs. 
	 */
	private function getPayload()
	{
		$payload = $this->packStringAsXdr($this->value);
		return $payload;
		// DEBUG incomplete
	}
	
	private function packIntAsXdr($int)
	{
		// DEBUG incomplete
		return "NYI";
	}
	
	private function packStringAsXdr($string)
	{
		// DEBUG incomplete
		return "NYI" . $string;
	}

}
