# Laravel Bounceable Job
Package for creating bounceable queue jobs in Laravel.

## Installation
`composer require frutality/laravel-bounceable-job`

## Why
Often our `Jobs` are trying to reach remote APIs which are down. Fail happens. Usually we want to retry these jobs several times in case API became up.

By default, Laravel provides ability to immediately retry failed job several times (see [docs](https://laravel.com/docs/5.6/queues#max-job-attempts-and-timeout)).

But in most cases, immediate retry will also fail because APIs need some time to get up. We may want to increase delay after each failed job try.

## Example
```
<?php

namespace App\Jobs;

use App\Something;
use App\Services\SomeService;
use Frutality\BounceableJob\Contracts\BounceableJob;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SentSomethingToAPI extends BounceableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $something;
    
    /**
     * Create a new job instance.
     *
     * @param Something $something
     */
    public function __construct(Something $something)
    {
        parent::__construct($something); // required
        $this->something = $something;
    }
    
    /**
     * This is the only required method. 
     * sentToAPI method may throw an exception. And if it throws - BounceableJob do all the work
     * @throws Exception
     */
    public function doWork(): void
    {
        $service = new SomeService();
        $service->sentToAPI($this->something);
    }
}

``` 

## Configuration
By default, if job fails, it will retry in 1 minute. If that attempt fails too, next one will retry in 5 minutes. Next one in 30 minutes. And the last one in 1 hour.

If you need to tweak those values, just define your own `getAttemptsDelays` method, for example:

```
public function getAttemptsDelays(): array
{
    return [
        2 * 60, // 2 minutes
        15 * 60, // 15 minutes
        60 * 60, // 60 minutes etc
    ];
}
```
