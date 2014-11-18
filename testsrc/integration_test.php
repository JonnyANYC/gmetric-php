<?php
require __DIR__ . "/../vendor/autoload.php";

use jonnyanyc\Ganglia\Gmetric\Gmetric;
use jonnyanyc\Ganglia\Gmetric\GmetricMessage;

$gmetric = new Gmetric();
$gmetric->useConfigFile();

$gmetric->sendMetric("testapp.testmetric1", "app", "uint16", 2100, "count", GmetricMessage::ONE_HOUR /2, GmetricMessage::ONE_DAY, "monitoring-master1:monitoring-master1");
$gmetric->sendMetric("testapp.testmetric2", "app", "float", 1.23, "dollars", GmetricMessage::ONE_HOUR /2, GmetricMessage::ONE_DAY, "monitoring-master1:monitoring-master1");
$gmetric->sendMetric("testapp.testmetric3", "app", "string", "up", "active", GmetricMessage::ONE_HOUR /2, GmetricMessage::ONE_DAY, "monitoring-master1:monitoring-master1");

echo "Test complete.\n";
