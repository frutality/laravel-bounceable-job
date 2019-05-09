<?php

namespace Frutality\BounceableJob\Exceptions;

use Exception;

class JobAttemptsExceeded extends Exception
{
    protected $message = "Every job attempt has failed";
}
