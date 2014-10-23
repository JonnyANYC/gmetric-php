gmetric-php
===========

A very simple, lightweight PHP client for sending Gmetric data to a Ganglia node via UDP.

Ganglia automatically generates a graph for any new metric it sees. So tracking a new metric just takes one or two lines
of code.

## Installation

Install via [Composer](getcomposer.org):

```javascript
    "require": {
    	"jonnyanyc/gmetric-php": "*"
    }
```

## Usage

```php
$gmetric = new \jonnyanyc\Ganglia\Gmetric\Gmetric();
$gmetric->sendMetric("app1.job2.method3.failures", "app", "uint16", 3, "failures", 3600, 259200);
// send more metrics if needed
```

You can specify a host and port when you instantiate a Gmetric object if your local gmond daemon isn't running on localhost at UDP port 8649.

### Gmetric fields

* `name`: The name of the metric you're tracking. You can use namespacing to order the results, such as "section.metric".
* `group`: An arbitrary group under which the metric should appear in the Ganglia web interface. Collect all of your metrics under a group named "app", or separate them by app, service, job, consumer, etc.
* `type`: The data type of the value. Valid values are:
  * `uint16`
  * `int16`
  * `uint32`
  * `int32`
  * `float`
  * `double`
  * `string`
* `value`: The value that you want to record for the given metric. The data type should match the given type. 
* `unit`: The arbitrary units of your value. Examples: "seconds", "failures", "rows", etc.
* `valueTTL`: The normal lifetime for a value for this metric, in seconds. For example, the error count for an hourly batch job would have a lifetime of 1 hour (3600 seconds). 
* `metricTTL`: The amount of time to retain a graph for this metric after Ganglia hasn't received any new values. For example, if you set this to 3 days (259,200 seconds), then Ganglia will drop the graph after 3 days of inactivity. The prior data will be retained, so the graph will re-appear with historical data if you send a new value.

Not yet supported:
* `slope`
* spoofed hostname


## Performance
