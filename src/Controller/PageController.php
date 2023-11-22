<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends BaseController
{
    #[Route(path: '/condizioni', name: 'condizioni')]
    public function termsPage(Request $request): Response
    {
        return $this->render('condizioni.html.twig');
    }
}
