<?php
require __DIR__ . "/../vendor/autoload.php";

$start = microtime(true);

use jonnyanyc\Ganglia\Gmetric\Gmetric;

$gmetric = new Gmetric();
echo "Time to instantiate: ", microtime(true) - $start, " seconds", PHP_EOL;

$gmetric->sendMetric("testapp.testmetric1", "app", "uint16", 2100, "count", Gmetric::ONE_HOUR /2);
echo "Time to instantiate and send 1 metric: ", microtime(true) - $start, " seconds", PHP_EOL;

$gmetric->sendMetric("testapp.testmetric2", "app", "float", 1.23, "dollars", Gmetric::ONE_HOUR /2);
echo "Time to instantiate and send 2 metrics: ", microtime(true) - $start, " seconds", PHP_EOL;

$gmetric->sendMetric("testapp.testmetric3", "app", "string", "up", "active", Gmetric::ONE_HOUR /2);
$gmetric->sendMetric("testapp.testmetric1", "app", "uint16", 2100, "count", Gmetric::ONE_HOUR /2);
$gmetric->sendMetric("testapp.testmetric2", "app", "float", 1.23, "dollars", Gmetric::ONE_HOUR /2);
echo "Time to instantiate and send 5 metrics: ", microtime(true) - $start, " seconds", PHP_EOL;

$gmetric->sendMetric("testapp.testmetric3", "app", "string", "up", "active", Gmetric::ONE_HOUR /2);
$gmetric->sendMetric("testapp.testmetric1", "app", "uint16", 2100, "count", Gmetric::ONE_HOUR /2);
$gmetric->sendMetric("testapp.testmetric2", "app", "float", 1.23, "dollars", Gmetric::ONE_HOUR /2);
$gmetric->sendMetric("testapp.testmetric3", "app", "string", "up", "active", Gmetric::ONE_HOUR /2);
$gmetric->sendMetric("testapp.testmetric1", "app", "uint16", 2100, "count", Gmetric::ONE_HOUR /2);
echo "Time to instantiate and send 10 metrics: ", microtime(true) - $start, " seconds", PHP_EOL;

$secondStart = microtime(true);
$secondGmetric = new Gmetric();
echo "Time to instantiate a second instance: ", microtime(true) - $secondStart, " seconds", PHP_EOL;


echo "Test complete.\n";
