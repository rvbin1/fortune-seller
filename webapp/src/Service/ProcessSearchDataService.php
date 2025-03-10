<?php

namespace App\Service;

use App\Entity\Item;

readonly class ProcessSearchDataService
{
    public function __construct(private ShowItemsService $sis)
    {
    }

    /**
     * Processes search data.
     *
     * @param int $page
     * @param array{query?: string|null, crafting?: bool, mysticForge?: bool}|null $searchData
     * @return array{items: Item[], totalPages: int, currentPage: int}
     */
    public function processData(int $page, ?array $searchData): array
    {
        $query = $searchData['query'] ?? null;
        $crafting = $searchData['crafting'] ?? null;
        $mysticForge = $searchData['mysticForge'] ?? null;

        if (!empty($query) || !empty($crafting) || !empty($mysticForge)) {
            return $this->sis->showItemsPaginated($page, $query, $crafting, $mysticForge);
        }

        return $this->sis->showItemsPaginated($page);
    }
}
