gmetric-php
===========

A simple, lightweight PHP client for sending Gmetric data to a Ganglia node via UDP.

== Usage

```php
$gmetric = new jonnyanyc\Ganglia\Gmetric\Gmetric();
$gmetric->sendMetric("testapp.testmetric", "app", "uint16", 2100, "count", 120, 86400);
```

Specify a host and port if your local gmond daemon isn't running on localhost at UDP port 8649.

=== Gmetric fields


== Performance
