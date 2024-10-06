<?php

namespace App\Service;
use App\Client\DomusClient;
use App\Interface\ClientInterface;
use App\Util\StringUtil;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

class ScrapeService
{
    /**
     * @param DomusClient $domusClient
     */
    public function __construct(private readonly ClientInterface $domusClient) {}

    public function login(ResponseInterface $response): false|array
    {
        try {
            $html = $response->getContent();
            $crawler = new Crawler($html);
            try {
                $isLogged = ('La login utente oppure la password non sono corretti.' !== $crawler->filterXPath("//*[contains(text(),'La login utente oppure la password non sono corretti.')]")->text());
            } catch (Throwable $e) {
                $isLogged = true;
            }

            if ($isLogged) {
                $csrfToken = $crawler->filterXPath("//meta[@name='csrf-token']")->attr("content");
                $csrfParam = $crawler->filterXPath("//meta[@name='csrf-param']")->attr("content");

                $headers = $response->getInfo();
                $cookie = Cookie::fromString(str_replace('Set-Cookie: ', '', $headers["response_headers"][10]));
                return [
                    'csrf' => [
                        'param' => $csrfParam,
                        'token' => $csrfToken,
                    ],
                    'cookie' => $cookie,
                ];
            }

            return false;
        } catch (Throwable $e) {
            return false;
        }
    }

    public function retrieveStudent(ResponseInterface $response, $cookie): false|array
    {
        try {
            $html = $response->getContent();
            $crawler = new Crawler($html);

            $studentsLink = $crawler->filterXPath("//body//div[@class='x_panel']/div/a");
            $studentData = [];
            foreach ($studentsLink as $node) {
                $idStudent = explode("/", $node->getAttribute('href'))[3];
                if ($this->checkStudent($idStudent, $cookie)) {
                    $studentData[$idStudent] = StringUtil::extractStudentName($node->nodeValue);
                }
            }
            return $studentData;

        } catch (Throwable) {
            return false;
        }
    }

    private function checkStudent($idStudent, $cookie): bool
    {
        try {
            $response = $this->domusClient->checkStudent($idStudent, $cookie);
            $statusCode = $response->getStatusCode();
            return $statusCode === Response::HTTP_OK;
        } catch (Throwable $e) {
            return false;
        }
    }

    public function program(ResponseInterface $response): false|array
    {
        try {
            $jsString = str_replace("\\n", "", $response->getContent());
            $htmlContentPattern = '/\$\(\'#data_details\'\)\.html\(\"(.*?)\"\);/s';
            $valuePatternProgram = '/\$\(\'#data_consegna\'\)\.val\(\'(.*?)\'\);/s';

            preg_match($htmlContentPattern, $jsString, $htmlMatches);
            preg_match($valuePatternProgram, $jsString, $valueMatches);

            $htmlContent = $htmlMatches[1] ?? null;
            $value = $valueMatches[1] ?? null;

            // The HTML content is escaped in the JS string, so we need to unescape it.
            if ($htmlContent !== null) {
                $htmlContent = stripslashes($htmlContent);
            }

            $array = [];
            $crawler = new Crawler($htmlContent);
            $crawler->filterXPath('//table[@id="programma_table"]/tbody/tr')->each(function (Crawler $rowNode) use (&$array) {
                $rowData = [];
                $p = 0;
                // XPath expression to select all cell elements in the row
                $rowNode->filterXPath('//td')->each(function (Crawler $cellNode) use (&$rowData, &$p) {
                    // Extracting the HTML content (not tags!) of each cell
                    $cellText = trim($cellNode->text());

                    // Extracting links if present
                    $links = $cellNode->filterXPath('.//a')->each(function (Crawler $link) {
                        return $link->attr('href');
                    });

                    $label = match ($p) {
                        0 => 'hour',
                        1 => 'subject',
                        2 => 'professor',
                        3 => 'program',
                        4 => 'link',
                    };

                    $rowData[$label] = ['text' => $cellText, 'links' => $links];
                    $p++;
                });
                $array[] = $rowData;
            });

            return [
                "table" => $array,
                "date" => $value
            ];
        } catch (Throwable) {
            return false;
        }
    }

    public function assignment(ResponseInterface $response): false|array
    {
        try {
            $jsString = str_replace("\\n", "<br>", $response->getContent());
            $htmlContentPattern = '/\$\(\'#materia_data_details\'\)\.html\(\"(.*?)\"\);/s';
            $valuePatternAssignment = '/\$\(\'#data_consegna\'\)\.val\(\'(.*?)\'\);/s';

            preg_match($htmlContentPattern, $jsString, $htmlMatches);
            preg_match($valuePatternAssignment, $jsString, $valueMatches);

            $htmlContent = $htmlMatches[1] ?? null;
            $value = $valueMatches[1] ?? null;

            // The HTML content is escaped in the JS string, so we need to unescape it.
            if ($htmlContent !== null) {
                $htmlContent = stripslashes($htmlContent);
            }

            $array = [];
            $crawler = new Crawler($htmlContent);
            $crawler->filterXPath('//table[@id="compiti_data_table"]/tbody/tr')->each(function (Crawler $rowNode) use (&$array) {
                $rowData = [];
                // XPath expression to select all cell elements in the row
                $i = 0;
                $rowNode->filterXPath('//td')->each(function (Crawler $cellNode) use (&$rowData, &$i) {
                    // Extracting the HTML content (not tags!) of each cell
                    if ($i === 0) {
                        $cellTextArray = explode(" ", trim($cellNode->text()));
                        $cellText = ['date' => $cellTextArray[0], 'string' => trim($cellNode->text())];
                    } else if ($i === 4) {
                        $cellText =  preg_replace('/^(<br\s*\/?>)*|(<br\s*\/?>)*$/i', '', preg_replace('#<br>(\s*<br>)+#', '<br>', strip_tags(trim($cellNode->html()), "<br>")));
                    } else {
                        $cellText = trim($cellNode->text());
                    }

                    // Extracting links if present
                    $links = $cellNode->filterXPath('.//a')->each(function (Crawler $link) {
                        return $link->attr('href');
                    });

                    $label = match ($i) {
                        0 => 'delivery',
                        1 => 'date',
                        2 => 'subject',
                        3 => 'professor',
                        4 => 'description',
                        5 => 'link',
                    };
                    if ($i === 1) {
                        $tmp = explode(" ", $cellText);
                        unset($cellText);
                        $cellText[0] = $tmp[0];
                        $cellText[1] = $tmp[1];
                    }
                    $rowData[$label] = ['text' => $cellText, 'links' => $links];
                    $i++;
                });
                $array[] = $rowData;
            });

            return [
                "table" => $array,
                "date" => $value
            ];
        } catch (Throwable $t) {
            return false;
        }
    }

    public function document(ResponseInterface $response, bool $all): false|array
    {
        try {
            $array = [];
            $html = $response->getContent();
            $crawler = new Crawler($html);
            $crawler->filterXPath('//table[@id="moduli_table"]/tbody/tr')->each(function (Crawler $rowNode) use (&$array) {
                $rowData = [];
                // XPath expression to select all cell elements in the row
                $i = 0;
                $rowNode->filterXPath('//td')->each(function (Crawler $cellNode) use (&$rowData, &$i) {

                    $cellText = trim($cellNode->text());
                    // Extracting links if present
                    $links = $cellNode->filterXPath('.//a')->each(function (Crawler $link) {
                        return $link->attr('href');
                    });

                    $label = match ($i) {
                        0 => 'description',
                        1 => 'date_from',
                        2 => 'date_to',
                        3 => 'link',
                    };

                    $rowData[$label] = ['text' => $cellText, 'links' => $links];
                    $i++;
                });
                $pathPart = explode('/', $rowData['link']['links'][0]);
                $idDoc = end($pathPart);
                $array[$idDoc] = $rowData;
            });

            return [
                "table" => $array,
            ];
        } catch (Throwable $t) {
            return false;
        }
    }
}
