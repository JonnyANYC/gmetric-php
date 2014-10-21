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

	/**
	 * Generate the header on-demand, based on the stored inputs. 
	 */
	public function getHeader()
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
	public function getPayload()
	{
		$payload = "";
		$this->packIntAsXdr($payload, 128 + 5);
		$this->packStringAsXdr($payload, "test");
		$this->packStringAsXdr($payload, $this->name);
		$this->packIntAsXdr($payload, 0);  // is spoofed
		$this->packStringAsXdr($payload, '%s');
		$this->packStringAsXdr($payload, (string)$this->value);

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

		$paddedLength = ceil(strlen($string) / 4) * 4;
		$buffer .= pack('a' . $paddedLength, $string);
	}

}
