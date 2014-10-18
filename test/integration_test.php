<?php
require __DIR__ . "/../vendor/autoload.php";


JonnyANYC\Ganglia\Gmetric\Gmetric::sendMetric("testapp.testmetric", "app", "uint16", 2100, "count", 360, 600);
