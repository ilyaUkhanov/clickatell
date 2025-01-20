<?php

namespace App\Service;

use App\Entity\Campaign;
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

    public function __construct(ServiceSendingList $serviceSendingList, ServiceCSV $serviceCSV,
                                ServiceClickatell $serviceClickatell)
    {
        $this->serviceSendingList = $serviceSendingList;
        $this->serviceCSV = $serviceCSV;
        $this->serviceClickatell = $serviceClickatell;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function advanceCampaign(Campaign $campaign): void
    {
        $file = $this->serviceSendingList->getFile($campaign->getSendingList());

        if(!$file) {
            throw new NotFoundHttpException("CSV file not found");
        }

        $decoded = $this->serviceCSV->decodeCSV($file);
        $columns = $this->serviceCSV->getColumns($file->getContent());

        foreach ($decoded as $row) {
            $template = $campaign->getTemplate();

            foreach ($columns as $column) {
                $template = str_replace( "{{" . $column . "}}", $row[$column], $template);
            }
            $this->serviceClickatell->sendMessage([$row['phone']], $template);
        }
    }
}
