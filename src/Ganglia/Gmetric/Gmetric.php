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
		try { 
			
			// Open the UDP socket to send the data.
			$socket = fsockopen("udp://" . $this->host, $this->port);
		
			if (!$socket) {
				// TODO: Log.warn: "Socket failed to open"
				throw new Exception("Cancelling send.");
			}
	
			socket_set_blocking($socket, FALSE);
		
			// Send the header.
			$header = $message->getHeader();
			$bytesWritten = fwrite($socket, $header);
		
			if ($bytesWritten < strlen($header)) {
				// TODO: Log.warn "Only wrote $bytesWritten bytes of the header."
				throw new Exception("Cancelling send.");
			}
		
			// Send the payload.
			$payload = $message->getPayload();
			$bytesWritten = fwrite($socket, $payload);
		
			if ($bytesWritten < strlen($payload)) {
				// TODO: Log.warn "Only wrote $bytesWritten bytes of the payload."
				throw new Exception("Cancelling send.");
			}

			// Close the socket.
			fclose($socket);
		
			// dereference the handles.
			$socket = null;
			$header = null;
			$payload = null;
			
		} catch (Exception $e) { 

			// TODO: Move this to a finally block (PHP 5.5).
			if ($socket) {
				try { 
					fclose($socket);
				} catch (Exception $e2) {}
			}
			
			$socket = null;
			return;
		}
	}

	private function sendViaSocket()
	{
		throw new Exception("Not implemented yet.");
	}

	// TODO: Consider implementing a counter feature that stores incoming data in a static variable.
	// This is useful for repeated actions in a single PHP page view. It is not viable in scenarios that require thread safety.
	// The calling class can ask to send the summed data explicitly, or it will be sent at teardown (if not cleared).
	
}
