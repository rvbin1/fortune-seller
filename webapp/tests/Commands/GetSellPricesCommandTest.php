<?php

namespace App\Commands;

use App\Entity\Item;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GetSellPricesCommand extends Command
{
    protected static $defaultName = 'app:get-sell-prices';

    private EntityManagerInterface $entityManager;
    private HttpClientInterface $httpClient;

    public function __construct(EntityManagerInterface $entityManager, HttpClientInterface $httpClient)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->httpClient = $httpClient;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Fetches sell prices for sellable items')
            ->setHelp('This command retrieves sell prices from an API and updates each sellable item accordingly.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Retrieve sellable items from the repository.
        $repository = $this->entityManager->getRepository(Item::class);
        $queryBuilder = $repository->createQueryBuilder('i')
            ->where('i.sellable = :sellable')
            ->setParameter('sellable', true);
        $items = $queryBuilder->getQuery()->getResult();

        // Make an HTTP request to get the price data.
        $response = $this->httpClient->request('GET', 'https://api.example.com/prices');
        $content = $response->getContent();
        $pricesData = json_decode($content, true);

        // Iterate over items to update the price.
        foreach ($items as $item) {
            $gw2Id = $item->getGw2Id();
            // Find matching price data for the item.
            foreach ($pricesData as $priceData) {
                if (isset($priceData['id']) && $priceData['id'] == $gw2Id) {
                    // Get the unit_price from the buys array.
                    $unitPrice = $priceData['buys']['unit_price'] ?? null;
                    if ($unitPrice !== null) {
                        // Calculate the sell price by applying a 10% fee (i.e. 90% of the unit price).
                        $sellPrice = (int) round($unitPrice * 0.9);
                        $item->setPrice($sellPrice);
                    }
                }
            }
        }

        $this->entityManager->flush();
        $output->writeln('Sell prices updated successfully.');
        return Command::SUCCESS;
    }
}
