<?php

namespace App\Exception;

use Exception;
use Throwable;

class GenericException extends Exception {
    public static function create(string $message, Throwable $previous = null): self
    {
        return new self($message, 0, $previous);
    }
}
