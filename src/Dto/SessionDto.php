<?php

namespace App\Dto;

use App\Interface\RequestDtoInterface;

class SessionDto implements RequestDtoInterface
{
    private ?string $cookie = null;
    private ?string $param = null;
    private ?string $token = null;
    private ?array $students = null;

    public function getCookie(): ?string
    {
        return $this->cookie;
    }

    public function setCookie(?string $cookie): self
    {
        $this->cookie = $cookie;
        return $this;
    }

    public function getParam(): ?string
    {
        return $this->param;
    }

    public function setParam(?string $param): self
    {
        $this->param = $param;
        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;
        return $this;
    }

    public function getStudents(): ?array
    {
        return $this->students;
    }

    public function setStudents(?array $students): self
    {
        $this->students = $students;
        return $this;
    }
}
