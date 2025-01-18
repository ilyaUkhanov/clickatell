<?php

namespace App\Controller;

use App\Repository\SendingListRepository;
use App\Service\ServiceCSV;
use App\Service\ServiceSMS;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\SendingList;
use App\Form\SendingListType;
use Vich\UploaderBundle\Handler\DownloadHandler;

class SendingListController extends AbstractController
{
    #[Route('/sending-list', name: 'sending_list', methods: ['GET'])]
    public function index(SendingListRepository $repo): Response
    {
        $lists = $repo->findAll();

        return $this->render('sending-list/index.html.twig', [
            'sending_lists' => $lists,
            'path' => 'src/Controller/SendingListController.php',
        ]);
    }

    #[Route('/sending-list/{id}', name: 'sending_list_view', methods: ['GET'])]
    public function view($id, SendingListRepository $repo, ServiceCSV $serviceCSV): Response
    {
        if(is_numeric($id)) {
            $sendingList = $repo->find($id);
        }
        else {
            throw new NotFoundHttpException();
        }

        return $this->render('sending-list/view.html.twig', [
            'entry' => $sendingList,
            'columns' => $serviceCSV->getColumns($sendingList->getFile()->getContent()),
        ]);
    }

    #[Route('/sending-list/{id}/edit', name: 'sending_list_edit', methods: ['GET', 'POST'])]
    public function edit($id, Request $request, EntityManagerInterface $entityManager, SendingListRepository $repo, ServiceCSV $serviceCSV): Response
    {
        if($id === "new") {
            $sendingList = new SendingList();
        }
        else if(is_numeric($id)) {
            $sendingList = $repo->find($id);
        }
        else {
            throw new NotFoundHttpException();
        }

        $form = $this->createForm(SendingListType::class, $sendingList);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newSendingList = $form->getData();
            $entityManager->persist($newSendingList);
            $entityManager->flush();

            return $this->redirectToRoute('sending_list');
        }

        $file = $sendingList->getFile();
        $columns = $file ? $serviceCSV->getColumns($file->getContent()) : [];

        return $this->render('sending-list/new.html.twig', [
            'form' => $form,
            'entry' => $sendingList,
            'columns' => $columns
        ]);
    }

    #[Route('/sending-list/{id}/send', name: 'sending_list_send', methods: ['GET'])]
    public function send($id, ServiceSMS $serviceSMS, ServiceCSV $serviceCSV, SendingListRepository $repo): Response
    {
        if(is_numeric($id)) {
            $sendingList = $repo->find($id);
        }
        else {
            throw new NotFoundHttpException();
        }

        $decoded = $serviceCSV->decodeCSV($sendingList->getFile());
        dump($sendingList->getFile()->getContent());
        dump($decoded);
        dump($serviceCSV->getColumns($sendingList->getFile()->getContent()));

        return $this->json("OK");
    }

    #[Route('/sending-list/{id}/download', name: 'sending_list_download', methods: ['GET'])]
    public function openSendingListFile(SendingList $sendingList, DownloadHandler $downloadHandler): Response
    {
        return $downloadHandler->downloadObject($sendingList, 'file', null, true, false);
    }
}
