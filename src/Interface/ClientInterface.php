<?php

namespace App\Interface;

use Symfony\Contracts\HttpClient\ResponseInterface;

interface ClientInterface
{
    public function login(RequestDtoInterface $requestDto): ResponseInterface;
}
