<?php
require __DIR__ . "/../vendor/autoload.php";

$gmetric = new JonnyANYC\Ganglia\Gmetric\Gmetric("172.31.28.45", 8666);
$gmetric->sendMetric("testapp.testmetric", "app", "uint16", 2100, "count", 360, 600);
