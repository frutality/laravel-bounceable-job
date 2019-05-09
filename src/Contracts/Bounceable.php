<?php

namespace Frutality\BounceableJob\Contracts;

interface Bounceable
{
    /**
     * Array of integer numbers which represents delays in seconds for every next job attempt
     * @return array
     */
    public function getAttemptsDelays(): array;
    
    public function repeatJobAfterDelay(): void;
}
