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
	private $slope;
	private $spoof;
	private $isSpoofed;

	public function __construct($name, $group, $type, $value, $unit, $valueTTL, $metricTTL, $slope = null, $spoofedHostname = null)
	{
		// TODO: Throw an exception if we don't have correct info to form a valid Gmetric message.
		$this->name = $name;
		$this->group = $group;
		$this->type = $type;
		$this->value = (string)$value;
		$this->unit = $unit;
		$this->valueTTL = $valueTTL;
		$this->metricTTL = $metricTTL;
		
		switch (strtolower($slope)) { 
			case 'zero':
				$this->slope = 0;
				break;
			case 'positive':
				$this->slope = 1;
				break;
			case 'negative':
				$this->slope = 2;
				break;
			default:
				$this->slope = 3;
		}

		if (!is_null($spoofedHostname) && strlen($spoofedHostname) > 0) { 
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
								1 + $this->isSpoofed);  // number of extra name+value pairs

		
		if ($this->isSpoofed) { 

			$spoofFormat = 'Na' . $this->getPaddedLength('SPOOF_HOST');
			$spoofFormat .= 'Na' . $this->getPaddedLength($this->spoof);
				
			$header .= pack($spoofFormat, 	strlen('SPOOF_HOST'),
											'SPOOF_HOST', 
											strlen($this->spoof),
											$this->spoof);
		}

		if (!is_null($this->group) && strlen($this->group) > 0) { 

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
