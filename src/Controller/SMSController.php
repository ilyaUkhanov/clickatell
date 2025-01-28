<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SMSController extends AbstractController
{
    #[Route('/messages/callback', name: 'app_sms')]
    public function index(): Response
    {
        return $this->json([]);
    }
}
