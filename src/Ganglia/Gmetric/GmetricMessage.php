<?php
namespace JonnyANYC\Ganglia\Gmetric;

class GmetricMessage 
{
	private $name;
	private $group;
	private $type;
	private $value;
	private $unit;
	private $valueTTL;
	private $metricTTL;

	public function __construct($name, $group, $type, $value, $unit, $valueTTL, $metricTTL)
	{
		// TODO: Throw an exception if we don't have correct info to form a valid Gmetric message.
		$this->name = $name;
		$this->group = $group;
		$this->type = $type;
		$this->value = $value;
		$this->unit = $unit;
		$this->valueTTL = $valueTTL;
		$this->metricTTL = $metricTTL;
	}

	public function send()
	{
		$this->sendViaFileHandle();
	}

	private function sendViaFileHandle()
	{
		// Open the UDP socket to send the data.
		$socket = fsockopen("udp://localhost", 8649);
	
		if (!$socket) { 
			echo "no good!";  // DEBUG
			return;
		}
		
		socket_set_blocking($socket, FALSE);

		// Send the header.
		$header = $this->getHeader();
		//var_dump( $header );
		$bytesWritten = fwrite($socket, $header);
	
		echo "$bytesWritten of " . strlen($header) . "bytes in the header.";
		if ($bytesWritten < strlen($header)) { 
			echo "WARN: only wrote $bytesWritten bytes of the header."; // DEBUG
		}
		
		// Send the payload.
		$payload = $this->getPayload();
		//var_dump($payload);
		$bytesWritten = fwrite($socket, $payload);

		echo "$bytesWritten of " . strlen($payload) . "bytes in the payload.";
		if ($bytesWritten < strlen($payload)) {
			echo "WARN: only wrote $bytesWritten bytes of the payload."; // DEBUG
		}
		
		// Close the socket.
		fclose($socket);
		
		// dereference the handles.
		$socket = null;
		$header = null;
		$payload = null;
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
		$header = "";
		$this->packIntAsXdr($header, 128);
		$this->packStringAsXdr($header, "test");
		$this->packStringAsXdr($header, $this->name);
		$this->packIntAsXdr($header, 0);  // is spoofed
		$this->packStringAsXdr($header, $this->type);
		$this->packStringAsXdr($header, $this->name);
		$this->packStringAsXdr($header, $this->unit);
		$this->packIntAsXdr($header, 3); // slope
		$this->packIntAsXdr($header, $this->valueTTL);  // tmax
		$this->packIntAsXdr($header, $this->metricTTL);  // dmax

		// Add 1 extra field for the group param
		$this->packIntAsXdr($header, 1);  // Indicate how many extra name/value pairs.
		$this->packStringAsXdr($header, "GROUP");
		$this->packStringAsXdr($header, $this->group);
		// TODO Consider creating a generic XDR encoder that builds a formatter and passes all fields to pack().
		
		return $header;
	}

	/**
	 * Generate the payload on-demand, based on the stored inputs. 
	 */
	private function getPayload()
	{
		$payload = "";
		$this->packIntAsXdr($payload, 128 + 5);
		$this->packStringAsXdr($payload, "test");
		$this->packStringAsXdr($payload, $this->name);
		$this->packIntAsXdr($payload, 0);  // is spoofed
		$this->packStringAsXdr($payload, '%s');
		$this->packStringAsXdr($payload, $this->value . "");

		return $payload;
	}
	
	private function packIntAsXdr(&$buffer, $int)
	{
		$intBytes = pack('N', $int);
		$buffer .= $intBytes;
	}
	
	private function packStringAsXdr(&$buffer, $string)
	{
		$this->packIntAsXdr($buffer, strlen($string));
		
		$buffer .= $string;
		
		$overage = strlen($string) % 4;
		if ( $overage ) { 
			while ($overage < 4 ) {
				$buffer .= "\0";
				$overage++;
			}
		}
	}

}
