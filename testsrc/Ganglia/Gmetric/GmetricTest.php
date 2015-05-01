<?php

use jonnyanyc\Ganglia\Gmetric\Gmetric;
use jonnyanyc\Ganglia\Gmetric\GmetricMessage;

class GmetricTest extends PHPUnit_Framework_TestCase
{
    public function testSend()
    {
        $expectedGmetricMessage = new GmetricMessage("test.name", 
                                                     "test.group", 
                                                     "uint16", 
                                                     12345, 
                                                     "units",
                                                     60,  // 1 minute
                                                     2592000);  // 30 days

        $stubGmetric = $this->getMock('\jonnyanyc\Ganglia\Gmetric\Gmetric', array('send'));
        
        $stubGmetric->expects($this->once())
                    ->method('send')
                    ->with($this->equalTo($expectedGmetricMessage));
        
        $stubGmetric->sendMetric("test.name", "test.group", "uint16", 12345, "units");
    }

    public function testParseMissingConfigFile() { 
        $missingConfigFile = "./testsrc/doesnotexist/donotcreate.conf";
        $gmetric = new Gmetric($missingConfigFile);
        $this->assertEquals(array( array("localhost", 8649)), $gmetric->getDestinations());
        $this->assertEquals(null, $gmetric->getSourceHostname());
    }

    public function testParseUnicastConfigFile() { 
        $configFile = "gmond_unicast.conf";
        $gmetric = new Gmetric($configFile);
        $this->assertEquals(array( array("gcollector1", 8649)), $gmetric->getDestinations());
        $this->assertEquals("app1:app1", $gmetric->getSourceHostname());
    }

    public function testParseAwsOpsWorksConfigFile() { 
        $configFile = "gmond_aws.conf";
        $gmetric = new Gmetric($configFile);
        $this->assertEquals(array( array("172.31.28.45", 8666)), $gmetric->getDestinations());
        $this->assertEquals("monitoring-master1:monitoring-master1", $gmetric->getSourceHostname());
    }
        
    // TODO Add a few tests for the splitDestinationString() method.
}

