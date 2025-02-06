<?php

namespace App\Service;

use App\Entity\Campaign;
use App\Entity\State;
use DateInvalidTimeZoneException;
use DateMalformedStringException;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ServiceCampaign
{
    public ServiceSendingList $serviceSendingList;
    public ServiceCSV $serviceCSV;
    public ServiceClickatell $serviceClickatell;
    public EntityManagerInterface $entityManager;
    public LoggerInterface $logger;

    const STEP = 100;

    public function __construct(ServiceSendingList $serviceSendingList, ServiceCSV $serviceCSV,
                                ServiceClickatell $serviceClickatell, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->serviceSendingList = $serviceSendingList;
        $this->serviceCSV = $serviceCSV;
        $this->serviceClickatell = $serviceClickatell;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws DateMalformedStringException
     * @throws DateInvalidTimeZoneException
     */
    public function advanceCampaign(Campaign $campaign): void
    {
        $file = $this->serviceSendingList->getFile($campaign->getSendingList());

        if(!$file) {
            throw new NotFoundHttpException("CSV file not found");
        }

        $now = new DateTime('now');
        $now->setTimezone(new DateTimeZone($campaign->getTimezone()));

        // Send between 9am and 8pm only
        $minimumTime = DateTime::createFromFormat('U', time());
        $minimumTime->setTimezone(new DateTimeZone($campaign->getTimezone()));
        $minimumTime->setTime(9, 0);

        $maximumTime = clone $now;
        $maximumTime->setTimezone(new DateTimeZone($campaign->getTimezone()));
        $maximumTime->setTime(20, 0);

        if($now < $minimumTime) {
            $this->logger->log("info", "It's earlier than 9am");
            return;
        }

        if($now > $maximumTime) {
            $this->logger->log("info", "It's later than 8pm");
            return;
        }

        // Get campaign metadata
        $decoded = $this->serviceCSV->decodeCSV($file);
        $columns = $this->serviceCSV->getColumns($file->getContent());
        $metadata = $this->getCampaignMetadata($campaign);
        $currentSchedule = $metadata['dates'][0];
        $now = new DateTime("now");

        foreach ($metadata['dates'] as $schedule) {
            $date = clone $schedule['date'];
            if($now > $date) {
                $currentSchedule = $schedule;
            }
        }

        $nextCursor = min(($campaign->getCursor() + ServiceCampaign::STEP), $metadata['total'], $currentSchedule['cursor'] );

        for($index = $campaign->getCursor(); $index < $nextCursor; ++$index) {
            $row = $decoded[$index];

            try {
                $template = $campaign->getTemplate();
                foreach ($columns as $column) {
                    $template = str_replace( "{{" . $column . "}}", $row[$column], $template);
                }
                $this->serviceClickatell->sendMessage([$row['phone']], $template);
            }
            catch (Exception $exception) {
                $campaign->setCursor($index);
                $campaign->setState(State::Cancelled);
                $this->entityManager->persist($campaign);
                $this->entityManager->flush();

                throw new NotFoundHttpException($exception->getMessage());
            }
        }

        if($nextCursor >= $metadata['total']) {
            $campaign->setCursor($metadata['total']);
            $campaign->setState(State::Finished);
        }
        else {
            $campaign->setCursor($nextCursor);
            $this->entityManager->persist($campaign);
            $this->entityManager->flush();
        }

        $this->entityManager->persist($campaign);
        $this->entityManager->flush();
    }

    /**
     * @throws DateInvalidTimeZoneException
     */
    public function getCampaignMetadata(Campaign $campaign): array
    {
        $sendNumberOfTimes = 5;
        $total = $this->serviceSendingList->getNumberLines($campaign->getSendingList());
        $totalCampaignDays = $campaign->getDateEndTimezone()->diff($campaign->getDateStartTimezone())->format("%a");

        $dates = [];
        $date = clone $campaign->getDateStartTimezone();
        $dateCursor = 0;
        $now = new DateTime('now');

        for($i = 0; $i < $sendNumberOfTimes; $i++) {
            $dateCursor = $dateCursor + floor($total / $sendNumberOfTimes) + 1;
            $dateCursor = min($dateCursor, $total);

            $dates[$i] = [
                'date' => clone $date,
                'cursor' => $dateCursor,
                'passed' => $now > $date
            ];

            $addDays = floor($totalCampaignDays / $sendNumberOfTimes) + 1;
            $date->modify("+$addDays day");
            if($date > $campaign->getDateEndTimezone()) {
                $date = clone $campaign->getDateEndTimezone();
            }
        }

        return [
            'cursor' => $campaign->getCursor(),
            'total' => $total,
            'dates' => $dates
        ];
    }
}
