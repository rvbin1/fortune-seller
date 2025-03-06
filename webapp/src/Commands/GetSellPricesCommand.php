<?php

namespace App\Commands;

use App\Entity\Item;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(name: 'update:prices')]
class GetSellPricesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private HttpClientInterface $client,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Ruft die Verkaufsdaten aus der API von GW2 und gibt diese aus.');
    }

    /**
     * Fetch sell information for a list of items.
     *
     * @param Item[] $items
     * @return array<int, array<string, mixed>>
     */
    public function fetchSellInformation(array $items): array
    {
        $chunkedItems = array_chunk($items, 199);
        $allResults = [];

        foreach ($chunkedItems as $chunk) {
            $ids = [];
            foreach ($chunk as $item) {
                $ids[] = $item->getGw2Id();
            }

            $url = "https://api.guildwars2.com/v2/commerce/prices?ids=" . implode(',', $ids);
            $response = $this->client->request('GET', $url);
            $content = $response->getContent();
            $results = json_decode($content, true);

            if (is_array($results)) {
                foreach ($results as $result) {
                    if (is_array($result)) {
                        $allResults[] = $result;
                    }
                }
            }
        }

        /** @var array<int, array<string, mixed>> $allResults */
        return $allResults;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Item[] $items */
        $items = $this->entityManager->getRepository(Item::class)
            ->createQueryBuilder('i')
            ->where('i.sellable = :sellable')
            ->setParameter('sellable', true)
            ->getQuery()
            ->getResult();

        $sellData = $this->fetchSellInformation($items);
        $sellDataById = [];

        foreach ($sellData as $priceInfo) {
            if (
                !isset($priceInfo['id'], $priceInfo['buys']) ||
                !is_array($priceInfo['buys']) ||
                !isset($priceInfo['buys']['unit_price']) ||
                !is_numeric($priceInfo['buys']['unit_price'])
            ) {
                continue;
            }
            $sellDataById[$priceInfo['id']] = $priceInfo;
        }

        foreach ($items as $item) {
            $gw2Id = (int) $item->getGw2Id();
            if (isset($sellDataById[$gw2Id])) {
                $priceInfo = $sellDataById[$gw2Id];
                $unitPrice = (int) $priceInfo['buys']['unit_price'];
                $goldPrice = round($unitPrice * 0.9, 0, PHP_ROUND_HALF_UP);
                $item->setPrice($goldPrice);
            }
        }

        /** @var Item[] $notSellableItems */
        $notSellableItems = $this->entityManager->getRepository(Item::class)
            ->createQueryBuilder('i')
            ->where('i.sellable = :sellable')
            ->setParameter('sellable', false)
            ->getQuery()
            ->getResult();

        foreach ($notSellableItems as $notSellableItem) {
            $notSellableItem->setPrice(null);
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
