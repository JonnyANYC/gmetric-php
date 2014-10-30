<?php
require __DIR__ . "/../vendor/autoload.php";

$gmetric = new jonnyanyc\Ganglia\Gmetric\Gmetric("172.31.28.45", 8666);

$gmetric->sendMetric("testapp.testmetric1", "app", "uint16", 2100, "count", 1800, 86400, null, "monitoring-master1:monitoring-master1");
$gmetric->sendMetric("testapp.testmetric2", "app", "float", 1.23, "dollars", 1800, 86400, null, "monitoring-master1:monitoring-master1");
$gmetric->sendMetric("testapp.testmetric3", "app", "string", "up", "active", 1800, 86400, null, "monitoring-master1:monitoring-master1");

echo "Test complete.\n";
