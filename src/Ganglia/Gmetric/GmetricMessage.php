<?php
namespace jonnyanyc\Ganglia\Gmetric;


class GmetricMessage 
{
	private $name;
	private $group;
	private $type;
	private $value;
	private $unit;
	private $valueTTL;
	private $metricTTL;
	private $spoof;
	private $isSpoofed;
	private $slope;
	
	public function __construct($name, $group, $type, $value, $unit, $valueTTL = null, $metricTTL = null, $spoofedHostname = null, $counter = null) {

		// TODO: Filter invalid characters. 
		if (strlen($name) > 0) { 
			$this->name = $name;
		} else { 
			throw new Exception('"Name" must be a valid string.');
		}

		// A null (missing) group is valid input. The message is constructed properly in this case.
		// FIXME: Not sure I tested a null group thoroughly.
		// TODO: Filter invalid characters.
		$this->group = $group;

		// FIXME: Use an enum to enforce a valid type.
		$this->type = $type;

		// FIXME: Is an empty or null string a valid input?
		// TODO: Filter invalid characters.
		$this->value = (string)$value;

		// FIXME: Is an empty or null string a valid input?
		// TODO: Filter invalid characters.
		$this->unit = $unit;

		if ($valueTTL != null && is_int($valueTTL)) {
			$this->valueTTL = $valueTTL;
		} else { 
			$this->valueTTL = 60;
		}

		if ($metricTTL != null && is_int($metricTTL)) {
			$this->metricTTL = $metricTTL;
		} else {
		    // Note: the default metric TTL is 30 days, whereas the official binary's default is 0 (indefinite). 
			$this->metricTTL = 3600 * 24 * 30;
		}

		if ($spoofedHostname != null && strlen($spoofedHostname) > 0) { 
			// TODO: Filter invalid characters.
			$this->spoof = $spoofedHostname;
			$this->isSpoofed = 1;
		} else { 
			$this->spoof = gethostname();
			$this->isSpoofed = 0;
		}

		if (strtolower($counter) === 'counter') {
            $this->slope = 1;
		} else { 
            $this->slope = 3;
		}
	}

	/**
	 * Generate the header on-demand, based on the stored inputs. 
	 */
	public function getHeader() {
		
		$format = 'N';   // message type (128)
		$format .= 'Na' . $this->getPaddedLength($this->spoof);
		$format .= 'Na' . $this->getPaddedLength($this->name);
		$format .= 'N';  // is spoofed
		$format .= 'Na' . $this->getPaddedLength($this->type);
		$format .= 'Na' . $this->getPaddedLength($this->name);
		$format .= 'Na' . $this->getPaddedLength($this->unit);
		$format .= 'NNNN';  // slope, tmax, dmax, number of extra name+value pairs

		$extraFields = 0;
		if ($this->isSpoofed) { 
		    $extraFields++;
		}
		if ($this->group != null && strlen($this->group) > 0) { 
		    $extraFields++;
		}

		$header = pack($format, 	128,
								strlen($this->spoof),
								$this->spoof,
								strlen($this->name),
								$this->name, 
								$this->isSpoofed,
								strlen($this->type),
								$this->type, 
								strlen($this->name),
								$this->name,
								strlen($this->unit),
								$this->unit, 
								$this->slope,
								$this->valueTTL,  // tmax
								$this->metricTTL,  // dmax
								$extraFields);  // number of extra name+value pairs

		
		if ($this->isSpoofed) { 

			$spoofFormat = 'Na' . $this->getPaddedLength('SPOOF_HOST');
			$spoofFormat .= 'Na' . $this->getPaddedLength($this->spoof);
				
			$header .= pack($spoofFormat, 	strlen('SPOOF_HOST'),
											'SPOOF_HOST', 
											strlen($this->spoof),
											$this->spoof);
		}

		if ($this->group != null && strlen($this->group) > 0) { 

			$groupFormat = 'Na' . $this->getPaddedLength('GROUP');
			$groupFormat .= 'Na' . $this->getPaddedLength($this->group);
		
			$header .= pack($groupFormat,	strlen('GROUP'),
											'GROUP',
											strlen($this->group),
											$this->group);
		}
		
		return $header;
	}
	
	/**
	 * Generate the payload on-demand, based on the stored inputs. 
	 */
	public function getPayload() { 

		$format = 'N';  // message type
		$format .= 'Na' . $this->getPaddedLength($this->spoof);
		$format .= 'Na' . $this->getPaddedLength($this->name);
		$format .= 'N';  // is spoofed
		$format .= 'NA' . $this->getPaddedLength('%s');
		$format .= 'NA' . $this->getPaddedLength($this->value);
		
		return pack($format, 	128+5,  // message type
								strlen($this->spoof),
								$this->spoof,
								strlen($this->name),
								$this->name,
								$this->isSpoofed,
								strlen('%s'),
								'%s',  // format string?
								strlen($this->value),
								$this->value);				
	}
	
	private function getPaddedLength($string) { 
		return (int) ceil(strlen($string) / 4) * 4;
	}
}
