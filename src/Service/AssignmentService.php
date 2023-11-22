<?php

namespace App\Service;

use App\Dto\SessionDto;
use App\Exception\GenericException;
use App\Interface\ClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class AssignmentService
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
    public function assignment(SessionDto $sessionDto, string $date): false|array
    {
        $assignments = $this->domusClient->assignment($sessionDto, $date);
        $assignmentData = [];
        foreach ($assignments AS $studentId => $assignment) {
            $assignmentData[$studentId] = $this->scrapeService->assignment($assignment);
        }

        return $assignmentData;
    }
}
