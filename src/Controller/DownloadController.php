<?php

namespace App\Controller;

use App\Dto\SessionDto;
use App\Exception\DisconnectedException;
use App\Service\DocumentService;
use App\Util\UserUtil;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class DownloadController extends BaseController
{
    #[Route(path: '/download', name: 'download')]
    public function downloadLink(
        Request $request,
        DocumentService $documentService,
    ): Response
    {
        try {
            if (!UserUtil::isLogged($request->getSession())) {
                throw DisconnectedException::create();
            }

            $sessionDto = new SessionDto();
            $sessionDto->setParam($request->getSession()->get('csrf-param'));
            $sessionDto->setToken($request->getSession()->get('csrf-token'));
            $sessionDto->setCookie($request->getSession()->get('cookie'));
            $sessionDto->setStudents($request->getSession()->get('students'));

            $doc = $documentService->download($sessionDto, $request->query->get('f'));
            $response = new Response($doc['content']);
            $response->headers->set('Content-Disposition', $doc["disposition"]);
            return $response;
        } catch (Throwable $t) {
            $this->addFlash('error', $t->getMessage());
            $request->getSession()->clear();
            return new RedirectResponse($this->generateUrl('login'));
        }
    }
}
