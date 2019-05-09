<?php

namespace Frutality\BounceableJob\Contracts;

use Frutality\BounceableJob\Exceptions\JobAttemptsExceeded;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Throwable;

abstract class BounceableJob implements Bounceable
{
    use Dispatchable;
    use Queueable;
    use InteractsWithQueue;
    
    public $tries = 1;
    
    const MINUTE = 60;
    
    public $constructorArguments;
    
    public function __construct(...$args)
    {
        $this->constructorArguments = $args;
    }
    
    public function handle(): void
    {
        try {
            $this->doWork();
        } catch (Throwable $exception) {
            $this->repeatJobAfterDelay();
        }
    }
    
    abstract public function doWork(): void;
    
    public function repeatJobAfterDelay(): void
    {
        $delays = $this->getAttemptsDelays();
        $delayIndex = array_search($this->delay, $delays);
        if ($delayIndex === false) {
            $delayIndex = 0;
        } else {
            $delayIndex += 1;
        }
    
        if ($delayIndex >= sizeof($delays)) {
            $this->fail(new JobAttemptsExceeded());
            return;
        }
        
        $delay = $delays[$delayIndex];
        self::dispatch(...$this->constructorArguments)
            ->delay($delay);
    }
    
    public function getAttemptsDelays(): array
    {
        return [
            1 * self::MINUTE,
            5 * self::MINUTE,
            30 * self::MINUTE,
            60 * self::MINUTE
        ];
    }
}
