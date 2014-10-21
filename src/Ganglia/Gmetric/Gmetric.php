<?php
namespace jonnyanyc\Ganglia\Gmetric;


class Gmetric 
{
	private $host;
	private $port;

	public function __construct($host = null, $port = null) {
		
		if ($host == null) { 
			$this->host = "localhost";
		} else { 
			$this->host = $host;
		}
		
		if ($port == null) {
			$this->port = 8649;
		} else { 
			$this->port = $port;
		}
	}

	// TODO: Consider implementing a function that pulls the host and port settings from the /etc/ganglia/gmond.conf file. 
	
	public function sendMetric($name, $group, $type, $value, $unit, $valueTTL, $metricTTL, $sampleRate = null)
	{
		// TODO: Check if the $sampleRate param is provided, and if so, suppress the metric selectively.

		// Instantiate a Gmetric message using the input parameters.
		$message = new GmetricMessage($name, $group, $type, $value, $unit, $valueTTL, $metricTTL);
		$this->send($message);
		$message = null;
	}
	
	private function send($message) { 
		
		$this->sendViaFileHandle($message);
	}
	
	private function sendViaFileHandle($message)
	{
		// Open the UDP socket to send the data.
		$socket = fsockopen("udp://" . $this->host, $this->port);
	
		if (!$socket) {
			echo "Socket failed to open!";  // TODO: log as debug output
			return;
		}

		socket_set_blocking($socket, FALSE);
	
		// Send the header.
		$header = $message->getHeader();
		$bytesWritten = fwrite($socket, $header);
	
		if ($bytesWritten < strlen($header)) {
			echo "WARN: only wrote $bytesWritten bytes of the header."; // TODO: log as debug output
		}
	
		// Send the payload.
		$payload = $message->getPayload();
		$bytesWritten = fwrite($socket, $payload);
	
		if ($bytesWritten < strlen($payload)) {
			echo "WARN: only wrote $bytesWritten bytes of the payload."; // TODO: log as debug output
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

	// TODO: Consider implementing a counter feature that stores incoming data in a static variable.
	// This is useful for repeated actions in a single PHP page view. It is not viable in scenarios that require thread safety.
	// The calling class can ask to send the summed data explicitly, or it will be sent at teardown (if not cleared).
	
}
