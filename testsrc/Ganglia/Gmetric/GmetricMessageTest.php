<?php

use jonnyanyc\Ganglia\Gmetric\GmetricMessage;


class GmetricMessageTest extends PHPUnit_Framework_TestCase { 

	public function testGetHeader() {

		$unpackFormatter = "NmsgType/NhostnameLen/A4hostname/NnameLen/A12name/NisSpoof/NtypeLen/A8type/Nname2Len/A12name2"
			. "/NunitLen/A12unit/Nslope/NvalueTTL/NmetricTTL/NxFieldCount/NgroupFieldNameLen/A8groupFieldName/NgroupLen/A4group";

		$basicMessage = $this->createBasicMessage();
		$header = $basicMessage->getHeader();
		$unpackedHeader = unpack($unpackFormatter, $header);
		
		// TODO: Assert that the lengths are correct as well.
		// TODO: extract method
		$this->assertEquals($this->basicMessage["name"], $unpackedHeader["name"]);
		$this->assertEquals($this->basicMessage["group"], $unpackedHeader["group"]);
		$this->assertEquals($this->basicMessage["type"], $unpackedHeader["type"]);
		$this->assertEquals($this->basicMessage["unit"], $unpackedHeader["unit"]);
		$this->assertEquals($this->basicMessage["valueTTL"], $unpackedHeader["valueTTL"]);
		$this->assertEquals($this->basicMessage["metricTTL"], $unpackedHeader["metricTTL"]);
	}

	public function testGetPayload() {

		$unpackFormatter = "NmsgType/NhostnameLen/A4hostname/NnameLen/A12name/NisSpoof/NvarTemplateLen/A4varTemplate"
			. "/NvalueLen/A4value";

		$basicMessage = $this->createBasicMessage();
		$payload = $basicMessage->getPayload();
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
		"valueTTL" => 1,
		"metricTTL" => 2,
	);

	private function createBasicMessage() {

		$message = new GmetricMessage(	$this->basicMessage["name"],
										$this->basicMessage["group"],
										$this->basicMessage["type"],
										$this->basicMessage["value"],
										$this->basicMessage["unit"],
										$this->basicMessage["valueTTL"],
										$this->basicMessage["metricTTL"]);
		return $message;
	}

}
