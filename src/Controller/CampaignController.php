<?php

namespace App\Controller;

use App\Entity\Campaign;
use App\Form\CampaignOneType;
use App\Form\CampaignTwoType;
use App\Repository\CampaignRepository;
use App\Repository\SendingListRepository;
use App\Service\ServiceCSV;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Proxy;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class CampaignController extends AbstractController
{
    public function __construct(
        private readonly RequestStack $requestStack,
    )
    {
    }

    #[Route('/campaign', name: 'campaign')]
    public function index(CampaignRepository $repository): Response
    {
        return $this->render('campaign/index.html.twig', [
            'controller_name' => 'CampaignController',
            'campaigns' => $repository->findAll()
        ]);
    }

    #[Route('/campaign/{id}/view', name: 'campaign_view')]
    public function view($id, CampaignRepository $repository, ServiceCSV $serviceCSV): Response
    {
        $campaign = $repository->find($id);
        $file = $campaign->getSendingList()->getFile();
        $columns = $file ? $serviceCSV->getColumns($file->getContent()) : [];

        return $this->render('campaign/view.html.twig', [
            'controller_name' => 'CampaignController',
            'entry' => $campaign,
            'columns' => $columns
        ]);
    }

    #[Route('/campaign/{id}/edit/stage-one', name: 'campaign_edit_one', methods: ['GET', 'POST'])]
    public function edit($id, Request $request, CampaignRepository $repo, ServiceCSV $serviceCSV): Response
    {
        if($id === "new") {
            $campaign = new Campaign();
        }
        else if(is_numeric($id)) {
            $campaign = $repo->find($id);
        }
        else {
            throw new NotFoundHttpException();
        }

        $form = $this->createForm(CampaignOneType::class, $campaign);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->requestStack->getSession()->set('campaign-form-stage-one', $form->getData());
            return $this->redirectToRoute('campaign_edit_two');
        }

        return $this->render('campaign/new-one.html.twig', [
            'form' => $form,
            'entry' => $campaign
        ]);
    }

    #[Route('/campaign/edit/stage-two', name: 'campaign_edit_two', methods: ['GET', 'POST'])]
    public function edit_two(Request $request, CampaignRepository $campaignRepository, EntityManagerInterface $entityManager, SendingListRepository $sendingListRepository, ServiceCSV $serviceCSV): Response
    {
        /** @var Campaign $campaign */
        $campaign = $this->requestStack->getSession()->get('campaign-form-stage-one');
        if(!$campaign) return $this->redirectToRoute('campaign');
        if($campaign->getId()) $campaign = $campaignRepository->find($campaign->getId());

        $realSendingList = $sendingListRepository->find($campaign->getSendingList()->getId());

        $form = $this->createForm(CampaignTwoType::class, $campaign);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $campaign->setSendingList($realSendingList);
            $entityManager->persist($campaign);
            $entityManager->flush();

            return $this->redirectToRoute('campaign');
        }

        // Avoid proxy-loading so we can auto-inject the file into the entity
        if ($realSendingList instanceof Proxy) {
            $realSendingList->__load();
        }
        $file = $realSendingList->getFile();

        $columns = $file ? $serviceCSV->getColumns($file->getContent()) : [];

        return $this->render('campaign/new-two.html.twig', [
            'form' => $form,
            'entry' => $campaign,
            'columns' => $columns
        ]);
    }
}
