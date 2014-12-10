gmetric-php
===========

A very simple, lightweight PHP client for sending Gmetric data to a Ganglia node via UDP. This lets you integrate your PHP application with Ganglia and track app-specific metrics via the Ganglia web UI.

Ganglia automatically generates a graph for any new metric it sees. So tracking a new metric just takes one or two lines
of code in your application.

The message is sent optimistically, via one-way, non-blocking UDP datagrams. This keeps the cost of tracking metrics as close to zero as possible.



## Installation

The easiest way to install is to use [Composer](http://getcomposer.org):

```
composer require jonnyanyc/gmetric-php:~0.2.0
```

Or add the package to your composer.json file manually:

```javascript
    "require": {
    	"jonnyanyc/gmetric-php": "~0.2.0"
    }
```


## Usage

```php
$gmetric = new \jonnyanyc\Ganglia\Gmetric\Gmetric();

$gmetric->sendMetric("app1.job2.execution_time", "app", "float", 3.12, "hours");
$gmetric->sendMetric("app1.job2.method3.failures", "app", "uint16", 3, "failures");
// send more metrics if needed
```

If you're running `gmond` locally, then you can use the `Gmetric->useConfigFile()` method to set the connectivity details. 
This method reads the local `gmond` config file to set the host and port of the server that should receive your metrics. 
It also sets the correct name for your host when reporting your metrics.

If you prefer, you can define these settings when you instantiate the Gmetric object. Or you can use the defaults: send 
metrics to localhost at UDP port 8649, and use your host's default host name when reporting metrics.
 

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
* `metricTTL`: The amount of time (in seconds) that Ganglia should still display the graph after not receiving any new values. For example, if you set this to 3 days (259,200 seconds), then Ganglia will drop the graph after 3 days of inactivity. The prior data will be retained, so the graph will re-appear with historical data if you send a new value.
* `counter`: Used to determine how the data should be stored in RRDtool. In most cases you'll want to use the default data-handling. You can pass a value of "counter" instead if your metric will only increase, in which case RRDtool will store the deltas. This is equivalent to passing a value of "positive" to the `slope` option of the Gmetric binary.



## Coming soon
  

## Performance

Details to come. In preliminary testing, sending a value appears to cost less than 1ms.
