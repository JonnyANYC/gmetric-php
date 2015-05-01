<?php
require __DIR__ . "/../vendor/autoload.php";

use jonnyanyc\Ganglia\Gmetric\Gmetric;

$start = microtime(true);

$gmetric = new Gmetric();
$gmetric->useConfigFile();

echo "Time to instantiate: ", microtime(true) - $start, PHP_EOL;
$gmetric->sendMetric("testapp.testmetric1", "app", "uint16", 2100, "count", Gmetric::ONE_HOUR /2);
$gmetric->sendMetric("testapp.testmetric2", "app", "float", 1.23, "dollars", Gmetric::ONE_HOUR /2);
$gmetric->sendMetric("testapp.testmetric3", "app", "string", "up", "active", Gmetric::ONE_HOUR /2);
$gmetric->sendMetric("testapp.testmetric1", "app", "uint16", 2100, "count", Gmetric::ONE_HOUR /2);
$gmetric->sendMetric("testapp.testmetric2", "app", "float", 1.23, "dollars", Gmetric::ONE_HOUR /2);
$gmetric->sendMetric("testapp.testmetric3", "app", "string", "up", "active", Gmetric::ONE_HOUR /2);
$gmetric->sendMetric("testapp.testmetric1", "app", "uint16", 2100, "count", Gmetric::ONE_HOUR /2);
$gmetric->sendMetric("testapp.testmetric2", "app", "float", 1.23, "dollars", Gmetric::ONE_HOUR /2);
$gmetric->sendMetric("testapp.testmetric3", "app", "string", "up", "active", Gmetric::ONE_HOUR /2);
$gmetric->sendMetric("testapp.testmetric1", "app", "uint16", 2100, "count", Gmetric::ONE_HOUR /2);

echo "Time to instantiate and send 10 metrics: $start + ", microtime(true) - $start, PHP_EOL;

echo "Test complete.\n";
