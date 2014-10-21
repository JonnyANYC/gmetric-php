<?php
require __DIR__ . "/../vendor/autoload.php";

$gmetric = new jonnyanyc\Ganglia\Gmetric\Gmetric("172.31.28.45", 8666);
$gmetric->sendMetric("testapp.testmetric", "app", "uint16", 2100, "count", 74, 600);
