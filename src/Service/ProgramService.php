<?php

namespace App\Service;

use App\Dto\SessionDto;
use App\Exception\GenericException;
use App\Interface\ClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ProgramService
{
    public function __construct(
        protected LoggerInterface $logger,
        protected ClientInterface $domusClient,
        protected ScrapeService $scrapeService,
        protected RequestStack $requestStack
    ){}

    /**
     * @throws GenericException
     */
    public function program(SessionDto $sessionDto, string $date): false|array
    {
        $programs = $this->domusClient->program($sessionDto, $date);
        $programData = [];
        foreach ($programs AS $studentId => $program) {
            $programData[$studentId] = $this->scrapeService->program($program);
        }

        return $programData;
    }
}
