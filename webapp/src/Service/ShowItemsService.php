<?php

namespace App\Service;

use App\Entity\Item;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

class ShowItemsService
{
    private const ITEMS_PER_PAGE = 10;

    /** @var string[] */
    private array $falseItem;

    /** @var string[] */
    private array $falseItemRegex;

    public function __construct(private readonly EntityManagerInterface $em)
    {
        $this->falseItem = ["Emote", "Mastery Point", "???"];
        $this->falseItemRegex = ["^\\(\\([0-9]+\\)\\)$", "^\\[\\[[0-9]+\\]\\]$"];
    }

    /**
     * @param int $page
     * @param string|null $searchString
     * @param bool|null $crafting
     * @param bool|null $mysticForge
     * @return array{items: Item[], totalPages: int, currentPage: int}
     */
    public function showItemsPaginated(int $page, ?string $searchString = null, ?bool $crafting = null, ?bool $mysticForge = null): array
    {
        $query = $this->em->getRepository(Item::class)
            ->createQueryBuilder('i')
            ->setFirstResult(($page - 1) * self::ITEMS_PER_PAGE)
            ->setMaxResults(self::ITEMS_PER_PAGE)
            ->where("i.sellable = 1")
            ->andWhere("i.name IS NOT NULL")
            ->andWhere("i.name <> ''");

        if ($searchString) {
            $query->andWhere("i.name LIKE :search")
                ->setParameter('search', '%'.$searchString.'%');
        }

        foreach ($this->falseItem as $falseItem) {
            $query->andWhere("i.name NOT LIKE :falseItem")
                ->setParameter("falseItem", '%'.$falseItem.'%');
        }

        foreach ($this->falseItemRegex as $falseItemRegex) {
            $query->andWhere("regexp(i.name, :falseItemRegex) = 0")
                ->setParameter("falseItemRegex", $falseItemRegex);
        }

        $query->orderBy('i.price', 'DESC');

        $query->getQuery();

        $paginator = new Paginator($query);

        /** @var Item[] $items */
        $items = iterator_to_array($paginator->getIterator());

        return [
            'items' => $items,
            'totalPages' => (int) ceil(count($paginator) / self::ITEMS_PER_PAGE),
            'currentPage' => $page,
        ];
    }
}
