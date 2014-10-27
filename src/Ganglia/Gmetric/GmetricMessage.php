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

		if (strlen($spoofedHostname) > 0  &&  !is_null($spoofedHostname)) { 
			$this->spoof = $spoofedHostname;
			$this->isSpoofed = 1;
		} else { 
			$this->spoof = 'none';
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
		$format .= 'Na' . $this->getPaddedLength('GROUP');
		$format .= 'Na' . $this->getPaddedLength($this->group);

		return pack($format, 	128,
								$this->getPaddedLength($this->spoof),
								$this->spoof,
								$this->getPaddedLength($this->name),
								$this->name, 
								$this->isSpoofed,
								$this->getPaddedLength($this->type),
								$this->type, 
								$this->getPaddedLength($this->name),
								$this->name,
								$this->getPaddedLength($this->unit),
								$this->unit, 
								$this->slope,
								$this->valueTTL,  // tmax
								$this->metricTTL,  // dmax
								1,  // number of extra name+value pairs
								$this->getPaddedLength('GROUP'),
								'GROUP', 
								$this->getPaddedLength($this->group),
								$this->group);
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
								$this->getPaddedLength($this->spoof),
								$this->spoof,
								$this->getPaddedLength($this->name),
								$this->name,
								$this->isSpoofed,
								$this->getPaddedLength('%s'),
								'%s',  // format string?
								$this->getPaddedLength($this->value),
								$this->value);				
	}
	
	private function getPaddedLength($string) { 
		return (int) ceil(strlen($string) / 4) * 4;
	}
}
