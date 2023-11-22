<?php

namespace App\Controller;

use App\Interface\ClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class BaseController extends AbstractController
{
    public function __construct(
        protected LoggerInterface $logger,
        protected ClientInterface $domusClient,
        protected RequestStack $requestStack
    ){}
}
