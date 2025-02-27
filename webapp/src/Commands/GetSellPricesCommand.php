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

#[AsCommand(name: 'update:prices')]
class GetSellPricesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SerializerInterface $serializer,
        private readonly KernelInterface $kernel,
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
        # to build the url for the api call
        $url = "https://api.guildwars2.com/v2/commerce/prices?ids=";
        foreach ($items as $id) {
            $url = $url . $id . ",";
        };

        # to remove the trailing ,
        $url = strrev($url);
        $url = substr($url, 1);
        $url = strrev($url);

        $response = $this->client->request(
            'GET',
            $url
        );

        $content = $response->getContent();

        return $content;
    }
}
