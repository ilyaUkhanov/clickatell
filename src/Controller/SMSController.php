<?php

namespace App\Controller;

use App\Entity\Banned;
use App\Repository\BannedRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SMSController extends AbstractController
{
    #[Route('/messages/callback', name: 'app_sms')]
    public function index(EntityManagerInterface $em, BannedRepository $repository): Response
    {
        $banned = new Banned();
        $banned->setPhone("33664550920");
        $em->persist($banned);
        $em->flush();

        $all = $repository->findAll();

        return $this->json([ $all ]);
    }
}
