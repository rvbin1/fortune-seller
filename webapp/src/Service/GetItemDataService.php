<?php

namespace App\Service;

use App\Entity\Item;
use Doctrine\ORM\EntityManagerInterface;

readonly class GetItemDataService
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function getItemData(int $id)
    {
        return $this->em->getRepository(Item::class)->find($id);
    }
}