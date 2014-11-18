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

	/**
	 * Send a metric to Ganglia.
	 * 
	 * @param string $name
	 * @param string $group
	 * @param string $type
	 * @param string $value
	 * @param string $unit
	 * @param int $valueTTL The amount of time that this measurement should be considered valid, in seconds. Defaults to 60.
	 * @param int $metricTTL The amount of time that this metric should be considered active if no measurements are received, 
	 *     in seconds. Defaults to 30 days. NOTE: This is different from the official binary, which defaults to 0 (indefinite).
	 * @param string $spoofedHostname
	 * @param string $counter Pass "counter" to instruct Ganglia to store the deltas of the given values. Otherwise, store the values as-is.
	 * @param float $sampleRate
	 */
    public function sendMetric( $name, 
                                $group, 
                                $type, 
                                $value, 
                                $unit, 
                                $valueTTL = null, 
                                $metricTTL = null, 
                                $spoofedHostname = null, 
                                $counter = null, 
                                $sampleRate = null) {

		// TODO: Check if the $sampleRate param is provided, and if so, suppress the metric selectively to reduce chatter.

		// Instantiate a Gmetric message using the input parameters.
		// TODO: Consider providing the default values for the inputs here, since this class owns the client interaction.
		$message = new GmetricMessage($name, $group, $type, $value, $unit, $valueTTL, $metricTTL, $spoofedHostname, $counter);
		$this->send($message);
		$message = null;
	}

	protected function send($message) { 
		
		$this->sendViaFileHandle($message);
	}
	
	private function sendViaFileHandle($message)
	{
		try { 
			
			// Open the UDP socket to send the data.
			$socket = @fsockopen("udp://" . $this->host, $this->port);
		
			if (!$socket) {
				// TODO: Log.warn: "Socket failed to open"
				// TODO: Use a finally block instead (PHP 5.5).
				throw new \Exception("Cancelling send.");
			}

			@socket_set_blocking($socket, FALSE);

			// Send the header.
			$header = $message->getHeader();
			$bytesWritten = @fwrite($socket, $header);
		
			if ($bytesWritten < strlen($header)) {
				// TODO: Log.warn "Only wrote $bytesWritten bytes of the header."
				// TODO: Use a finally block instead (PHP 5.5).
				throw new \Exception("Cancelling send.");
			}
		
			// Send the payload.
			$payload = $message->getPayload();
			$bytesWritten = @fwrite($socket, $payload);
		
			if ($bytesWritten < strlen($payload)) {
				// TODO: Log.warn "Only wrote $bytesWritten bytes of the payload."
				// TODO: Use a finally block instead (PHP 5.5).
				throw new \Exception("Cancelling send.");
			}

			// Close the socket.
			@fclose($socket);
		
			// dereference the handles.
			$socket = null;
			$header = null;
			$payload = null;
			
		} catch (\Exception $e) { 

			// TODO: Move this to a finally block (PHP 5.5).
			if ($socket) {
				try { 
					@fclose($socket);
				} catch (\Exception $e2) {}
			}
			
			$socket = null;
			return;
		}
	}

	private function sendViaSocket()
	{
		throw new \Exception("Not implemented yet.");
	}

	
	/**
	 * If called, this instance of Gmetric will use the configuration values in the local Gmond conf file.
	 * Additionally, this instance will conform to the logic of the official Gmetric binary as much as possible.
	 * This includes the following logic changes:
	 *     1) The host and port passed to the constructor are overridden by any values in the Gmond config file.
	 *     2) The spoof hostname value is set automatically if the config file specifies a host override.
	 *
	 * Currently, the Gmetric class won't use multiple UDP channels in the Gmond conf file. Only the first one will be used.
	 * Only unicast send channels are currently supported.
	 * Additionally, only the transport details are altered to match the official Gmetric binary.
	 * Other Gmetric settings remain distinct, such as the default values for the metric TTL (30 days for this class
	 * vs. indefinite for the binary). This is because the programatic context is inherently different from the
	 * command-line context.
	 * @param string $configFile The absolute path to the local Gmond conf file. Defaults to /etc/ganglia/gmond.conf.
	 */
	public function useConfigFile($configFile = '/etc/ganglia/gmond.conf') {
	
	    // FIXME: Filter inputs.
	    $configFile = @file_get_contents($configFile);
	
	    if (empty($configFile)) {
	        // TODO: Log a warning if the file isn't found
	        return;
	    }
	
	    // TODO: Follow include directives in the conf file.
	
	    // grep for 'udp_send_channel { [host|port] }'
	    $configUdpSendCount = preg_match_all('/udp_send_channel\s*\{(.*(host|port).*)\}/isU',
	                    $configFile,
	                    $configUdpSend,
	                    PREG_PATTERN_ORDER);
	
	    if (!$configUdpSendCount || !is_array($configUdpSend) || !is_array($configUdpSend[1])) {
	        return;
	    }
	
	    // TODO: Support more than one udp_send_channel.
	    $configUdpSendInner = $configUdpSend[1][0];
	
	    // grep for the destination host.
	    // TODO: Support UTF-8 host names.
	    $hostMatchCount = preg_match('/$\s*host\s*=\s*"?([\w\d:.-]*)"?\s*^/im', $configUdpSendInner, $hostMatches);
	    if ($hostMatchCount) {
	        $this->host = $hostMatches[1];
	    }
	
	    // grep for the destination port.
	    $portMatchCount = preg_match('/$\s*port\s*=\s*"?(\d+)"?\s*^/im', $configUdpSendInner, $portMatches);
	    if ($portMatchCount) {
	        $this->port = $portMatches[1];
	    }
	
	    // grep for host override in the global section.
	
	    // TODO: check if the official binary use the cluster config value. I think it only uses the cmd-line cluster value if any.
	}

	
	// TODO: Consider implementing a counter feature that stores incoming data in a static variable.
	// The calling class can ask to send the summed data explicitly, or it will be sent at teardown (if not cleared).
	
}
