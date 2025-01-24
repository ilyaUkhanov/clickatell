<?php

namespace App\Controller;

use App\Entity\Campaign;
use App\Form\CampaignOneType;
use App\Form\CampaignTwoType;
use App\Repository\CampaignRepository;
use App\Repository\SendingListRepository;
use App\Service\ServiceCSV;
use App\Service\ServiceSendingList;
use Doctrine\ORM\EntityManagerInterface;
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
    public function edit_two(Request $request, CampaignRepository $campaignRepository,
                             EntityManagerInterface $entityManager, SendingListRepository $sendingListRepository,
                             ServiceCSV $serviceCSV, ServiceSendingList $serviceSendingList): Response
    {
        /** @var Campaign $campaign */
        $sessionCampaign = $this->requestStack->getSession()->get('campaign-form-stage-one');
        $campaign = $sessionCampaign;

        if(!$sessionCampaign) return $this->redirectToRoute('campaign');
        if($sessionCampaign->getId()) $campaign = $campaignRepository->find($sessionCampaign->getId());

        $realSendingList = $sendingListRepository->find($sessionCampaign->getSendingList()->getId());
        $campaign->setSendingList($realSendingList);

        $form = $this->createForm(CampaignTwoType::class, $campaign);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $campaign->setSendingList($realSendingList);
            $entityManager->persist($campaign);
            $entityManager->flush();

            return $this->redirectToRoute('campaign');
        }

        $file = $serviceSendingList->getFile($realSendingList);
        $columns = $file ? $serviceCSV->getColumns($file->getContent()) : [];

        return $this->render('campaign/new-two.html.twig', [
            'form' => $form,
            'entry' => $campaign,
            'columns' => $columns
        ]);
    }

    #[Route('/campaign/{id}/delete', name: 'campaign_delete', methods: ['GET'])]
    public function delete(Campaign $campaign, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($campaign);
        $entityManager->flush();

        return $this->redirectToRoute('campaign');
    }
}
