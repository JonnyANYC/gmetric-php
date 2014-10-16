<?php
require __DIR__ . "/../vendor/autoload.php";


JonnyANYC\Ganglia\Gmetric\Gmetric::sendMetric("testapp.testmetric", "UINT16", 2100, "count", "app");
