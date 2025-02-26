<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class ProcessSearchData
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function processData($searchData)
    {
        if (is_array($searchData) && !empty($searchData))
        {

        }
    }

    private function findItems($query, $crafting = false, $selling = false, $convert = false)
    {

    }
}