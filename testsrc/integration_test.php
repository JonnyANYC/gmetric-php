<?php
require __DIR__ . "/../vendor/autoload.php";

$gmetric = new jonnyanyc\Ganglia\Gmetric\Gmetric("172.31.28.45", 8666);

$gmetric->sendMetric("testapp.testmetric1", "app", "uint16", 2100, "count", 74, 86400);
$gmetric->sendMetric("testapp.testmetric2", "app", "float", 1.23, "dollars", 74, 86400);
$gmetric->sendMetric("testapp.testmetric3", "app", "string", "up", "active", 74, 86400);

echo "Test complete.";
