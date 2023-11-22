<?php

namespace App\Util;

use Symfony\Component\HttpFoundation\Session\Session;

class UserUtil
{
    public static function isLogged(Session $session): bool
    {
        return $session->get("cookie") !== null &&
            $session->get("csrf-param") !== null &&
            $session->get("students") !== null;
    }
}
