<?php

use jonnyanyc\Ganglia\Gmetric\GmetricMessage;


class GmetricMessageTest extends PHPUnit_Framework_TestCase { 

    public function testDefaults() { 

        $expected = new GmetricMessage($this->basicMessage["name"],
                                       $this->basicMessage["group"],
                                       $this->basicMessage["type"],
                                       $this->basicMessage["value"],
                                       $this->basicMessage["unit"], 
                                       $this->basicMessage["valueTTL"], 
                                       $this->basicMessage["metricTTL"],
                                       'positive');

        $message = new GmetricMessage($this->basicMessage["name"],
                        $this->basicMessage["group"],
                        $this->basicMessage["type"],
                        $this->basicMessage["value"],
                        $this->basicMessage["unit"], 
                        $this->basicMessage["valueTTL"],
                        $this->basicMessage["metricTTL"]);
        
        
        $this->assertEquals($expected, $message);
    }

    /**
     * @dataProvider messageDataProvider
     */
	public function testGetHeader($messageParams, $message) {

		$hostname = gethostname();
		$hostnameLen = (int) ceil(strlen($hostname) / 4) * 4;
		$unpackFormatter = 'NmsgType/NhostnameLen/A' . $hostnameLen . 'hostname/NnameLen/A12name/NisSpoof/NtypeLen'
			. '/A8type/Nname2Len/A12name2/NunitLen/A12unit/Nslope/NvalueTTL/NmetricTTL/NxFieldCount';
			
		if (isset($messageParams['group'])) { 
		    $unpackFormatter .= '/NgroupFieldNameLen/A8groupFieldName/NgroupLen/A4group';
		}

		$header = $message->getHeader();
		$unpackedHeader = unpack($unpackFormatter, $header);
		
		// TODO: Assert that the lengths are correct as well.
		// TODO: extract method
		$this->assertEquals($this->basicMessage['name'], $unpackedHeader['name']);

		if (isset($messageParams['group'])) { 
		    $this->assertEquals(1, $unpackedHeader['xFieldCount']);
		    $this->assertEquals($messageParams['group'], $unpackedHeader['group']);
		}

		$this->assertEquals($this->basicMessage["type"], $unpackedHeader["type"]);
		$this->assertEquals($this->basicMessage["unit"], $unpackedHeader["unit"]);
		
		if (isset($messageParams['valueTTL'])) { 
            $this->assertEquals($messageParams['valueTTL'], $unpackedHeader["valueTTL"]);
		}

		if (isset($messageParams['metricTTL'])) { 
		    $this->assertEquals($messageParams['metricTTL'], $unpackedHeader["metricTTL"]);
		}
	}

    /**
     * @dataProvider messageDataProvider
     */
	public function testGetPayload($messageParams, $message) {

		$hostname = gethostname();
		$hostnameLen = (int) ceil(strlen($hostname) / 4) * 4;
		
		$unpackFormatter = "NmsgType/NhostnameLen/A" . $hostnameLen . "hostname/NnameLen/A12name/NisSpoof"
			. "/NvarTemplateLen/A4varTemplate/NvalueLen/A4value";

		$payload = $message->getPayload();
		$unpackedPayload = unpack($unpackFormatter, $payload);
		
		// TODO: Assert that the lengths are correct as well.
		$this->assertEquals('%s', $unpackedPayload["varTemplate"]);
		$this->assertEquals($this->basicMessage["value"], $unpackedPayload["value"]);

	}
	
	private $basicMessage = array(
		"name" => "test.test",
		"group" => "test", 
		"type" => "uint16",
		"value" => -1,
		"unit" => "test units",
		"valueTTL" => 3600,
		"metricTTL" => 186400,
	);

	public function messageDataProvider() {

	    $inputs = array();

		$inputs[] = array( 
		    array('group' => $this->basicMessage["group"]),
            new GmetricMessage($this->basicMessage["name"],
                               $this->basicMessage["group"],
                               $this->basicMessage["type"],
                               $this->basicMessage["value"],
                               $this->basicMessage["unit"],
                               $this->basicMessage["valueTTL"],
                               $this->basicMessage["metricTTL"])
                            
        );

		$inputs[] = array( 
		    array('group' => $this->basicMessage["group"],
		          'valueTTL' => $this->basicMessage["valueTTL"],
                  'metricTTL' => $this->basicMessage["metricTTL"]),
		    new GmetricMessage($this->basicMessage["name"],
                               $this->basicMessage["group"],
                               $this->basicMessage["type"],
                               $this->basicMessage["value"],
                               $this->basicMessage["unit"], 
		                       $this->basicMessage["valueTTL"],
                               $this->basicMessage["metricTTL"])
        );

		$inputs[] = array( 
		    array(),
            new GmetricMessage($this->basicMessage["name"],
                               null,
                               $this->basicMessage["type"],
                               $this->basicMessage["value"],
                               $this->basicMessage["unit"],
                               $this->basicMessage["valueTTL"],
                               $this->basicMessage["metricTTL"])
                            
        );

		return $inputs;
	}
}
