gmetric-php
===========

A very simple, lightweight PHP client for sending Gmetric data to a Ganglia node via UDP. This lets you integrate your PHP application with Ganglia and track app-specific metrics via the Ganglia web UI.

Ganglia automatically generates a graph for any new metric it sees. So tracking a new metric just takes one or two lines
of code in your application.

The message is sent optimistically, via one-way, non-blocking UDP datagrams. This keeps the cost of tracking metrics as close to zero as possible.


8
## Installation

The easiest way to install is to use [Composer](http://getcomposer.org):

```
composer require jonnyanyc/gmetric-php:~0.3.0
```

Or add the package to your composer.json file manually:

```javascript
    "require": {
    	"jonnyanyc/gmetric-php": "~0.3.0"
    }
```


## Usage

```php
$gmetric = new \jonnyanyc\Ganglia\Gmetric\Gmetric();

$gmetric->sendMetric("app1.job2.execution_time", "app", "float", 3.12, "hours");
$gmetric->sendMetric("app1.job2.method3.failures", "app", "uint16", 3, "failures");
// send more metrics if needed
```

The Gmetric class gets its configuration settings from a `gmond` config file. If you're running `gmond` locally, then you 
already have one. The class looks for this file in '/etc/ganglia/gmond.conf' by default, or you can specify an alternate path at 
construction:

```php
$gmetric = new \jonnyanyc\Ganglia\Gmetric\Gmetric('/etc/gmetric.conf');
```

Alternatively, you can use the `setDestinations()` method to specify which host should receive the metrics. And you can use the 
`setSourceHostname` method to indicate what host name should be reported as the source of your metrics.


### Gmetric fields

* `name`: The name of the metric you're tracking.
* `group`: An arbitrary group under which the metric should appear in the Ganglia web interface. Collect all of your app-specific metrics under a group named "app", or separate them by service, job, consumer, type (such as error counts), etc.
* `type`: The data type of the value. Valid values are:
  * `uint16`
  * `int16`
  * `uint32`
  * `int32`
  * `float`
  * `double`
  * `string`
* `value`: The value that you want to record for the given metric.
* `unit`: The arbitrary units of your value, which is displayed as the Y axis on the graph. Examples: "seconds", "failures", "rows", etc.
* `valueTTL`: The normal lifetime for a value for this metric, in seconds. For example, the error count for an hourly batch job would have a lifetime of 1 hour (3600 seconds).
* `metricTTL`: The amount of inactivity (in seconds) after which Ganglia should drop this graph from its display. For example, if you set this to 3 days (259,200 seconds), then Ganglia will drop the graph if it doesn't receive any data for this metric for 3 days. The prior data will be retained, so the graph will re-appear with historical data if you send a new value.
* `counter`: Used to determine how the data should be stored in RRDtool. In most cases you'll want to use the default data-handling. You can pass a value of "counter" instead if your metric will only increase, in which case RRDtool will store the deltas. This is equivalent to passing a value of "positive" to the `slope` option of the Gmetric binary.



## Coming soon

* Support for multicast UDP.

## Performance

Some preliminary results on an AWS micro EC2 host:
* Instiating the Gmetric class (and reading the config file): ~ 1.5 milliseconds
* Sending a metric ~ 400 microseconds or less
    
You should be able to send a dozen metrics in less than 5 milliseconds.