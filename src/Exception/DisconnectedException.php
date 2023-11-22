<?php

namespace App\Exception;

use Exception;
use Throwable;

class DisconnectedException extends Exception {
    public static function create(Throwable $previous = null): self
    {
        return new self('La sessione è scaduta.', 0, $previous);
    }
}
