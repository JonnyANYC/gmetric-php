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

    // TODO Add several tests for the useConfigFile() method, if I can fake out the calls to the file system.
    
    // TODO Add a few tests for the splitDestinationString() method.
}

