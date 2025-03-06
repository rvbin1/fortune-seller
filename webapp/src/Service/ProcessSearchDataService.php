<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

readonly class ProcessSearchDataService
{
    public function __construct(private ShowItemsService $sis)
    {
    }

    public function processData($page ,$searchData)
    {

        if (is_array($searchData) && !empty($searchData))
        {
            if ($searchData['query'] != null && $searchData['query'] !== '' ||
                $searchData['crafting'] != null && $searchData['crafting'] !== '' ||
                $searchData['mysticForge'] != null && $searchData['mysticForge'] !== '')
            {
                return $this->sis->showItemsPaginated($page, $searchData['query'], $searchData['crafting'], $searchData['mysticForge']);
            }
        }
    }
}