<?php

namespace App\Service;

use App\Entity\SendingList;
use App\Repository\SendingListRepository;
use Doctrine\Persistence\Proxy;

class ServiceSendingList
{
    public SendingListRepository $repository;
    public ServiceCSV $serviceCSV;

    public function __construct(SendingListRepository $repository, ServiceCSV $serviceCSV) {
        $this->repository = $repository;
        $this->serviceCSV = $serviceCSV;
    }

    public function getFile(SendingList $sendingList)
    {
        $realSendingList = $this->repository->find($sendingList->getId());
        if ($realSendingList instanceof Proxy) {
            $realSendingList->__load();
        }
        return $realSendingList->getFile();
    }

    public function getNumberLines(SendingList $sendingList): int
    {
        $file = $this->getFile($sendingList);
        $data = $this->serviceCSV->cleanupCSV($file->getContent());
        return substr_count( $data, "\n" );
    }
}
