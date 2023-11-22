<?php

namespace App\Client;

use App\Dto\LoginDto;
use App\Dto\SessionDto;
use App\Exception\DisconnectedException;
use App\Exception\GenericException;
use App\Interface\ClientInterface;
use App\Interface\RequestDtoInterface;
use App\Util\DateUtil;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

readonly class DomusClient implements ClientInterface
{
    public function __construct(private HttpClientInterface $domusClient) {}

    /**
     * @param LoginDto $requestDto
     * @return ResponseInterface
     * @throws GenericException
     */
    public function login(RequestDtoInterface $requestDto): ResponseInterface
    {
        try {
            return $this->domusClient->request(
                method: 'POST',
                url: '/?method=POST',
                options: [
                    'body' => [
                        'login' => $requestDto->getUsername(),
                        'password' => $requestDto->getPassword()
                    ],
                ]
            );
        } catch (Throwable $t) {
            throw GenericException::create($t->getMessage());
        }
    }

    /**
     * @throws GenericException
     */
    public function retrieveStudent($cookie): ResponseInterface
    {
        try {
            return $this->domusClient->request(
                method: 'GET',
                url: '/ref_programma/index',
                options: [
                    'headers' => [
                        'Cookie' => $cookie
                    ]
                ]
            );
        } catch (Throwable $t) {
            throw GenericException::create($t->getMessage());
        }
    }

    /**
     * @return ResponseInterface[]
     * @throws GenericException
     */
    public function program(SessionDto $sessionDto, string $date): array
    {
        try {
            $program = [];
            foreach ($sessionDto->getStudents() AS $studentId => $studentName) {
                $program[$studentId] = $this->domusClient->request(
                    method: 'POST',
                    url: '/ref_programma/change_data',
                    options: [
                        'headers' => [
                            'Accept' => 'text/javascript, */*; q=0.01',
                            'X-Requested-With' => 'XMLHttpRequest',
                            'Cookie' => $sessionDto->getCookie()
                        ],
                        'body' => [
                            'id_alunno' => $studentId,
                            'data_programma' => DateUtil::convertToItalianDate($date),
                            $sessionDto->getParam() => $sessionDto->getToken(),
                        ],
                    ]
                );

                /* the session cookie has expired */
                if ($program[$studentId]->getContent() === "window.location = 'https://webscuola.scuolabraschi.it/';") {
                    throw DisconnectedException::create();
                }
            }
            return $program;
        } catch (Throwable $t) {
            throw GenericException::create($t->getMessage());
        }
    }

    /**
     * @return ResponseInterface[]
     * @throws GenericException
     */
    public function assignment(SessionDto $sessionDto, string $date): array
    {
        try {
            $assignment = [];
            foreach ($sessionDto->getStudents() AS $studentId => $studentName) {
                $assignment[$studentId] = $this->domusClient->request(
                    method: 'POST',
                    url: '/ref_compiti/change_data',
                    options: [
                        'headers' => [
                            'Accept' => 'text/javascript, */*; q=0.01',
                            'X-Requested-With' => 'XMLHttpRequest',
                            'Cookie' => $sessionDto->getCookie()
                        ],
                        'body' => [
                            'id_alunno' => $studentId,
                            'data_consegna' => DateUtil::convertToItalianDate($date),
                            $sessionDto->getParam() => $sessionDto->getToken(),
                        ],
                    ]
                );

                /* the session cookie has expired */
                if ($assignment[$studentId]->getContent() === "window.location = 'https://webscuola.scuolabraschi.it/';") {
                    throw DisconnectedException::create();
                }
            }
            return $assignment;
        } catch (Throwable $t) {
            throw GenericException::create($t->getMessage());
        }
    }
}
