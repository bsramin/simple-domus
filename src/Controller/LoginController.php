<?php

namespace App\Controller;

use App\Service\LoginService;
use App\Util\UserUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
class LoginController extends BaseController
{
    #[Route(path: '/', name: 'login')]
    public function loginPage(Request $request, LoginService $loginService): Response
    {
        if (UserUtil::isLogged($request->getSession())) {
            return $this->redirectToRoute("dashboard");
        }

        if ($request->isMethod('POST')) {
            if (
                $loginService->login($request->request->get("username"), $request->request->get("password")) &&
                $loginService->retrieveStudent()
            ) {
                return $this->redirectToRoute("dashboard");
            }

            $this->addFlash('error', 'Dati non validi. Alunno non trovato.');
        }
        return $this->render('login.html.twig');
    }

    #[Route(path: '/logout', name: 'logout')]
    public function logout(Request $request): Response
    {
        if (UserUtil::isLogged($request->getSession())) {
            $request->getSession()->clear();
        }

        return $this->redirectToRoute("login");
    }
}
