<?php

use jonnyanyc\Ganglia\Gmetric\Gmetric;
use jonnyanyc\Ganglia\Gmetric\GmetricMessage;

class GmetricTest extends PHPUnit_Framework_TestCase
{
    public function testSend()
    {
        $stubGmetric = $this->getMock('\jonnyanyc\Ganglia\Gmetric\Gmetric', array('send'));
        
        $expectedGmetricMessage = new GmetricMessage("test.name", 
                                                     "test.group", 
                                                     "uint16", 
                                                     12345, 
                                                     "units");
        
        $stubGmetric->expects($this->once())
                    ->method('send')
                    ->with($this->equalTo($expectedGmetricMessage));
        
        $stubGmetric->sendMetric("test.name", "test.group", "uint16", 12345, "units");
    }
}

