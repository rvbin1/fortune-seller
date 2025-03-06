<?php

namespace App\Commands;

use App\Entity\Item;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(name: 'update:prices')]
class GetSellPricesCommand extends Command
{
    private const COPPER = 'copper';
    private const SILVER = 'silver';
    private const GOLD = 'gold';
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
    public function fetchSellInformation(array $items): array
    {
        $chunkedItems = array_chunk($items, 199);
        $allResults = [];

        foreach ($chunkedItems as $chunk) {
            $ids = [];
            foreach ($chunk as $item) {
                if (!$item instanceof Item) {
                    continue;
                }
                $ids[] = $item->getGw2Id();
            }
            if (empty($ids)) {
                continue;
            }

            $url = "https://api.guildwars2.com/v2/commerce/prices?ids=" . implode(',', $ids);

            $response = $this->client->request('GET', $url);
            $content = $response->getContent();

            $results = json_decode($content, true);
            if (is_array($results)) {
                $allResults = array_merge($allResults, $results);
            }
        }

        return $allResults;
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $items = $this->entityManager->getRepository(Item::class)
            ->createQueryBuilder('i')
            ->where('i.sellable = :sellable')
            ->setParameter('sellable', true)
            ->getQuery()
            ->getResult();

        $sellData = $this->fetchSellInformation($items);

        $sellDataById = [];
        foreach ($sellData as $priceInfo) {
            $sellDataById[(int) $priceInfo['id']] = $priceInfo;
        }

        foreach ($items as $item) {
            if (!$item instanceof Item) {
                continue;
            }
            $gw2Id = (int) $item->getGw2Id();
            if (isset($sellDataById[$gw2Id])) {
                $goldPrice = round( ($sellDataById[$gw2Id]['buys']['unit_price'] * 0.9), 0, PHP_ROUND_HALF_UP);
                $item->setPrice($goldPrice);
            }
        }

        $notSellableItems = $this->entityManager->getRepository(Item::class)
            ->createQueryBuilder('i')
            ->where('i.sellable = :sellable')
            ->setParameter('sellable', false)
            ->getQuery()
            ->getResult();

        foreach ($notSellableItems as $notSellableItem) {
            if (!$notSellableItem instanceof Item) continue;
            $notSellableItem->setPrice(null);
        }

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
