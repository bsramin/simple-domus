<?php

namespace App\Service;

use App\Client\DomusClient;
use App\Dto\SessionDto;
use App\Exception\GenericException;
use App\Interface\ClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

class DocumentService
{
    /**
     * @param DomusClient $domusClient
     */
    public function __construct(
        protected LoggerInterface $logger,
        protected ClientInterface $domusClient,
        protected ScrapeService $scrapeService,
        protected RequestStack $requestStack
    ){}

    /**
     * @throws GenericException
     */
    public function list(SessionDto $sessionDto): array
    {
        $docs = $this->domusClient->document($sessionDto);
        foreach ($docs AS $studentId => $doc) {
            $docs[$studentId] = $this->scrapeService->document($doc, $all = false);
        }
        return $docs;
    }

    /**
     * @throws Throwable
     */
    public function download(SessionDto $sessionDto, $path): array
    {
        $file = $this->domusClient->downloadDocument($sessionDto, $path);

        return [
            'content' => $file->getContent(),
            'disposition' => $file->getHeaders()['content-disposition'][0]
        ];
    }
}
