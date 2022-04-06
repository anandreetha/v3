<?php

namespace Multiple\Backend\Exceptions;

use Exception;

class FileSizeException extends Exception
{
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
    }

    // custom string representation of object
    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}