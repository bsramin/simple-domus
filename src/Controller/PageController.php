<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends BaseController
{
    #[Route(path: '/dashboard/condizioni', name: 'condizioni')]
    public function termsPage(Request $request): Response
    {
        return $this->render('condizioni.html.twig');
    }

    #[Route(path: '/condizioni', name: 'condizioni_public')]
    public function termsPublicPage(Request $request): Response
    {
        return $this->render('condizioni_public.html.twig');
    }
}
