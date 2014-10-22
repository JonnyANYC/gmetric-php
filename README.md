gmetric-php
===========

A simple, lightweight PHP client for sending Gmetric data to a Ganglia node via UDP.

Ganglia automatically generates a graph for any new metric it sees. So tracking a new metric just takes a line or two 
of code.

## Usage

```php
$gmetric = new jonnyanyc\Ganglia\Gmetric\Gmetric();
$gmetric->sendMetric("testapp.testmetric", "app", "uint16", 2100, "count", 120, 86400);
// send more metrics if needed
```

Specify a host and port if your local gmond daemon isn't running on localhost at UDP port 8649.

### Gmetric fields

* `name`: The name of the metric you're tracking. You can use namespacing to order the results, such as "section.metric".
* `group`: An arbitrary group under which the metric should appear in the Ganglia web interface.
* `type`: The data type of the value. Valid values are:
  ** `string`
  ** `uint16`
  ** `int16`
  ** `uint32`
  ** `int32`
  ** `float`
  ** `double`
* `value`: The value that you want to record for the given metric. The data type should match the given type. 
* `unit`: The arbitrary units of your value. Examples: "seconds", "failures", "rows", etc.
* `valueTTL`: The normal lifetime for a value for this metric, in seconds. For example, the error count for a daily batch job would have a lifetime of 1 day (in seconds). 
* `metricTTL`: The amount of time to retain a graph for this metric after Ganglia hasn't received any new values. For example, if you set this to 3 days (in seconds), then Ganglia will drop the graph after 3 days of inactivity. The prior data will be retained, so the graph will re-appear with historical data if you send a new value.

Not yet supported:
* `slope`
* spoofed hostname


## Performance
