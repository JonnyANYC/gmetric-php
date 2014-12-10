<?php
namespace jonnyanyc\Ganglia\Gmetric;

use Exception;

class GmetricMessage 
{
    // Arithmetic expressions aren't allowed when defining constants? Really, PHP?
    // @deprecated
    const ONE_MINUTE = 60;
    const ONE_HOUR = 3600;
    const ONE_DAY = 86400;
 
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
	
	public function __construct($name, $group, $type, $value, $unit, $valueTTL, $metricTTL, $counter = null, $spoofedHostname = null) {

		// TODO: Filter invalid characters. 
		if (!empty($name)) { 
			$this->name = $name;
		} else { 
			throw new Exception('"name" must be a valid string.');
		}

		// A null (missing) group is valid input. The message is constructed properly in this case.
		// FIXME: Not sure I tested a null group thoroughly.
		// TODO: Filter invalid characters.
		$this->group = $group;

		// FIXME: Use an enum to enforce a valid type.
		if (!empty($type)) {
		    $this->type = $type;
		} else {
		    throw new Exception('"type" must be a valid Gmetric type.');
		}
		
		// FIXME: Is an empty or null string a valid input? Gmetric binary only requires that the option appears on the cmd line.
		// TODO: At the least, validate that inputs are numeric for the numeric types.
		// TODO: Filter invalid characters.
		$this->value = (string)$value;

		// FIXME: Is an empty or null string a valid input?
		// TODO: Filter invalid characters.
		$this->unit = $unit;

		if (!is_int($valueTTL)) {
			throw new Exception('"valueTTL" must be an integer.');
		} else { 
		    $this->valueTTL = $valueTTL;
		}

		if (!is_int($metricTTL)) {
			throw new Exception('"metricTTL" must be an integer.');
		} else { 
		    $this->metricTTL = $metricTTL;
		}

		if (strtolower($counter) === 'counter') {
            $this->slope = 1;
		} else { 
            $this->slope = 3;
		}

		if (!empty($spoofedHostname)) { 
		    if (strpos($spoofedHostname, ":") === false) { 
		        throw new Exception('The "spoofed hostname" is invalid. It must be of the form "ip:host".');
		    }
			// TODO: Filter invalid characters.
			$this->spoof = $spoofedHostname;
			$this->isSpoofed = 1;
		} else { 
			$this->spoof = gethostname();
			$this->isSpoofed = 0;
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
		$format .= 'Na' . $this->getPaddedLength('%s');  // format string
		$format .= 'Na' . $this->getPaddedLength($this->value);
		
		return pack($format, 	128+5,  // message type
								strlen($this->spoof),
								$this->spoof,
								strlen($this->name),
								$this->name,
								$this->isSpoofed,
								strlen('%s'),
								'%s',
								strlen($this->value),
								$this->value);				
	}
	
	private function getPaddedLength($string) { 
		return (int) ceil(strlen($string) / 4) * 4;
	}
}
