<?php

namespace App\Service;

use App\Entity\SendingList;
use App\Repository\SendingListRepository;
use Doctrine\Persistence\Proxy;

class ServiceSendingList
{
    public SendingListRepository $repository;

    public function __construct(SendingListRepository $repository) {
        $this->repository = $repository;
    }

    public function getFile(SendingList $sendingList)
    {
        $realSendingList = $this->repository->find($sendingList->getId());
        if ($realSendingList instanceof Proxy) {
            $realSendingList->__load();
        }
        return $realSendingList->getFile();
    }
}
