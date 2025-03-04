<?php

namespace App\Service;

use App\Entity\Item;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

class ShowItemsService
{
    private const ITEMS_PER_PAGE = 10;

    private array $falseItem;
    private array $falseItemRegex;
    public function __construct(private readonly EntityManagerInterface $em)
    {
       $this->falseItem = ["Emote", "Mastery Point", "???"];
       $this->falseItemRegex = ["^\\(\\([0-9]+\\)\\)$", "^\\[\\[[0-9]+\\]\\]$"];
    }

    public function showItemsPaginated(int $page, ?string $searchString = null): array
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

        return [
            'items' => $paginator->getIterator()->getArrayCopy(),
            'totalPages' => ceil(count($paginator) / self::ITEMS_PER_PAGE),
            'currentPage' => $page,
        ];
    }
}
