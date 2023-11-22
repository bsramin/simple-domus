<?php

namespace App\Service;

use App\Dto\LoginDto;
use App\Interface\ClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Throwable;

class LoginService
{
    public function __construct(
        protected LoggerInterface $logger,
        protected ClientInterface $domusClient,
        protected ScrapeService $scrapeService,
        protected RequestStack $requestStack
    ){}

    public function login(string $username, string $password): bool
    {
        try {
            $loginDto = new LoginDto();
            $loginDto->setUsername($username);
            $loginDto->setPassword($password);

            $response = $this->domusClient->login($loginDto);
            $loginData = $this->scrapeService->login($response);

            if ($loginData) {
                $session = $this->requestStack->getSession();
                $session->set('csrf-token', $loginData["csrf"]["token"]);
                $session->set('csrf-param', $loginData["csrf"]["param"]);
                $session->set('cookie', $loginData["cookie"]);
                return true;
            }

            return false;
        } catch (Throwable) {
            return false;
        }
    }

    public function retrieveStudent(): bool
    {
        try {
            $session = $this->requestStack->getSession();
            $response = $this->domusClient->retrieveStudent($session->get('cookie'));
            $studentData = $this->scrapeService->retrieveStudent($response);
            $session->set('students', $studentData);
            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
