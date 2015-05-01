<?php
require __DIR__ . "/../vendor/autoload.php";

use jonnyanyc\Ganglia\Gmetric\Gmetric;

$gmetric = new Gmetric();

$gmetric->sendMetric("testapp.testmetric1", "app", "uint16", 2100, "count", Gmetric::ONE_HOUR /2, Gmetric::ONE_DAY);
$gmetric->sendMetric("testapp.testmetric2", "app", "float", 1.23, "dollars", Gmetric::ONE_HOUR /2, Gmetric::ONE_DAY);
$gmetric->sendMetric("testapp.testmetric3", "app", "string", "up", "active", Gmetric::ONE_HOUR /2, Gmetric::ONE_DAY);

echo "Test complete.\n";
