<?php

namespace App\Command;

use App\Entity\State;
use App\Repository\CampaignRepository;
use App\Service\ServiceCampaign;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsCommand(
    name: 'app:advance-campaigns',
    description: 'Add a short description for your command',
)]
class AdvanceCampaignsCommand extends Command
{
    public ServiceCampaign $serviceCampaign;
    public CampaignRepository $campaignRepository;

    /**
     * @param ServiceCampaign $serviceCampaign
     * @param CampaignRepository $campaignRepository
     */
    public function __construct(ServiceCampaign $serviceCampaign, CampaignRepository $campaignRepository)
    {
        parent::__construct();
        $this->serviceCampaign = $serviceCampaign;
        $this->campaignRepository = $campaignRepository;
    }


    protected function configure(): void
    {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->success('Advancing campaigns');

        $campaigns = $this->campaignRepository->findBy([ 'state' => State::Launched->value ]);
        foreach($campaigns as $campaign) {
            $io->success('Advancing campaign - ' . $campaign->getName() . " - ID #" . $campaign->getId());
            $this->serviceCampaign->advanceCampaign($campaign);
        }

        $io->success("Success !");

        return Command::SUCCESS;
    }
}
