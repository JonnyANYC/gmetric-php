<?php
namespace jonnyanyc\Ganglia\Gmetric;

use Exception;

class Gmetric 
{
    // Arithmetic expressions aren't allowed when defining constants? Really, PHP?
    const ONE_MINUTE = 60;
    const ONE_HOUR = 3600;
    const ONE_DAY = 86400;
    const THIRTY_DAYS = 2592000;

    private $destHost;
	private $destPort;
	private $myHostname;

	public function __construct($destHost = null, $destPort = null, $myHostname = null) {
		
		if (is_null($destHost)) { 
			$this->destHost = "localhost";
		} else { 
			$this->destHost = $destHost;
		}
		
		if (is_null($destPort)) {
			$this->destPort = 8649;
		} else { 
			$this->destPort = $destPort;
		}
		
		if (!is_null($myHostname)) { 
		    $this->myHostname = $myHostname;
		} else { 
		    $this->myHostname = null;
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
	 * @param string $counter Pass "counter" to instruct Ganglia to store the deltas of the given values. Otherwise, store the values as-is.
	 * @param float $sampleRate
	 */
    public function sendMetric( $name, 
                                $group, 
                                $type, 
                                $value, 
                                $unit, 
                                $valueTTL = self::ONE_MINUTE, 
                                $metricTTL = self::THIRTY_DAYS, 
                                $counter = null, 
                                $sampleRate = null) {

		// TODO: Check if the $sampleRate param is provided, and if so, suppress the metric selectively to reduce chatter.

		// Instantiate a Gmetric message using the input parameters.
		// TODO: Consider providing the default values for the inputs here, since this class owns the client interaction.
		$message = new GmetricMessage($name, $group, $type, $value, $unit, $valueTTL, $metricTTL, $counter, $this->myHostname);
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
			$socket = @fsockopen("udp://" . $this->destHost, $this->destPort);
		
			if (!$socket) {
				// TODO: Log.warn: "Socket failed to open"
				// TODO: Use a finally block instead (PHP 5.5).
				throw new Exception("Cancelling send.");
			}

			@socket_set_blocking($socket, FALSE);

			// Send the header.
			$header = $message->getHeader();
			$bytesWritten = @fwrite($socket, $header);
		
			if ($bytesWritten < strlen($header)) {
				// TODO: Log.warn "Only wrote $bytesWritten bytes of the header."
				// TODO: Use a finally block instead (PHP 5.5).
				throw new Exception("Cancelling send.");
			}
		
			// Send the payload.
			$payload = $message->getPayload();
			$bytesWritten = @fwrite($socket, $payload);
		
			if ($bytesWritten < strlen($payload)) {
				// TODO: Log.warn "Only wrote $bytesWritten bytes of the payload."
				// TODO: Use a finally block instead (PHP 5.5).
				throw new Exception("Cancelling send.");
			}

			// Close the socket.
			@fclose($socket);
		
			// dereference the handles.
			$socket = null;
			$header = null;
			$payload = null;
			
		} catch (Exception $e) { 

			// TODO: Move this to a finally block (PHP 5.5).
			if ($socket) {
				try { 
					@fclose($socket);
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
	
	    // Grep for 'udp_send_channel { [host|port] }'
	    // TODO: Support multicast send channels.
	    // TODO: Extract method.
	    $configUdpSendCount = preg_match_all('/(^|\n)\s*udp_send_channel\s*(\{.*(host|port).*\})/isU',
	                    $configFile,
	                    $configUdpSend,
	                    PREG_PATTERN_ORDER);
	
	    if (!$configUdpSendCount || !is_array($configUdpSend) || !is_array($configUdpSend[2])) {
	        return;
	    }
	
	    // TODO: Support more than one udp_send_channel.
	    $configUdpSendInner = $configUdpSend[2][0];
	
	    // grep for the destination host.
	    // TODO: Support UTF-8 host names.
	    $hostMatchCount = preg_match('/$\s*host\s*=\s*"?([\w\d:.-]+)"?\s*^/im', $configUdpSendInner, $hostMatches);
	    if ($hostMatchCount) {
	        $this->destHost = $hostMatches[1];
	    }
	
	    // grep for the destination port.
	    $portMatchCount = preg_match('/$\s*port\s*=\s*"?(\d+)"?\s*^/im', $configUdpSendInner, $portMatches);
	    if ($portMatchCount) {
	        $this->destPort = $portMatches[1];
	    }
	
	    // Grep for host override in the global section. If found, then force a spoofed host name.
	    // TODO: Extract method.
	    $configGlobalsCount = preg_match_all('/(^|\n)\s*globals\s*(\{.*override_hostname.*\})/isU',
	                    $configFile,
	                    $configGlobals,
	                    PREG_PATTERN_ORDER);

	     
	    if (!$configGlobalsCount || !is_array($configGlobals) || !is_array($configGlobals[2])) {
	        return;
	    }
	    

        $configGlobalsInner = $configGlobals[2][0];
        
        // grep for the override IP and host.
        $overrideHostCount = preg_match('/$\s*override_hostname\s*=\s*"?([\w\d:.-]+)"?\s*^/im', $configGlobalsInner, $overrideHostMatches);
        $overrideIpCount = preg_match('/$\s*override_ip\s*=\s*"?([\d:.]+)"?\s*^/im', $configGlobalsInner, $overrideIpMatches);
        
        if ($overrideHostCount) {

            if ($overrideIpCount) {
                $this->myHostname = $overrideIpMatches[1] . ":";
            } else { 
                $this->myHostname = $overrideHostMatches[1] . ":";
            }
            
            $this->myHostname .= $overrideHostMatches[1];
        }

        
	    // TODO: check if the official binary use the cluster config value. I think it only uses the cmd-line cluster value if any.
	}

	
	// TODO: Consider implementing a counter feature that stores incoming data in a static variable.
	// The calling class can ask to send the summed data explicitly, or it will be sent at teardown (if not cleared).
	
}
